<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_a_guest_is_sent_to_the_login_form(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_it_shows_your_own_data(): void
    {
        $user = User::factory()->create(['name' => 'Ismael', 'surname' => 'De la Rosa']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Ismael')
            ->assertSee('De la Rosa');
    }

    public function test_the_three_states_of_the_verification_are_told_apart(): void
    {
        $missing = User::factory()->pendingReview()->create();
        $this->actingAs($missing)->get(route('profile.show'))->assertSee('Te faltan los papeles');

        $pending = User::factory()->pendingReview()->create([
            'document_photo_path' => 'documents/dni.jpg',
            'licence_photo_path' => 'licences/carne.jpg',
        ]);
        $this->actingAs($pending)->get(route('profile.show'))->assertSee('Estamos mirando tus papeles');

        $verified = User::factory()->create();
        $this->actingAs($verified)->get(route('profile.show'))->assertSee('Identidad verificada');
    }

    public function test_someone_can_change_their_data(): void
    {
        $user = User::factory()->create(['phone' => '600000000']);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Ismael',
            'surname' => 'De la Rosa Guerrero',
            'email' => $user->email,
            'phone' => '611223344',
            'birthdate' => '1995-03-14',
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('De la Rosa Guerrero', $user->surname);
        $this->assertSame('611223344', $user->phone);
    }

    public function test_keeping_your_own_email_is_not_a_duplicate(): void
    {
        $user = User::factory()->create(['email' => 'ismael@aparcado.test']);

        // Sin el `ignore()` de la regla, guardar sin tocar el correo daría «ya hay
        // una cuenta con ese correo».
        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'ismael@aparcado.test',
            'phone' => '611223344',
            'birthdate' => '1995-03-14',
        ])->assertSessionHasNoErrors();
    }

    public function test_it_will_not_take_the_email_of_somebody_else(): void
    {
        User::factory()->create(['email' => 'lucia@aparcado.test']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => 'lucia@aparcado.test',
            'phone' => '611223344',
            'birthdate' => '1995-03-14',
        ])->assertSessionHasErrors('email');
    }

    public function test_the_avatar_goes_to_the_public_disk(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'phone' => '611223344',
            'birthdate' => '1995-03-14',
            'avatar' => UploadedFile::fake()->image('yo.jpg'),
        ])->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_the_papers_go_to_the_private_disk_and_never_to_the_public_one(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->pendingReview()->create();

        $this->actingAs($user)->post(route('profile.documents'), [
            'document_photo' => UploadedFile::fake()->image('dni.jpg'),
            'licence_photo' => UploadedFile::fake()->image('carne.jpg'),
        ])->assertRedirect();

        $user->refresh();

        /*
         * Lo importante de este test: en el TFG el DNI y el carné vivían en una
         * carpeta pública del servidor, así que con acertar el nombre del fichero
         * cualquiera se descargaba el documento de otro.
         */
        Storage::disk('local')->assertExists($user->document_photo_path);
        Storage::disk('local')->assertExists($user->licence_photo_path);
        Storage::disk('public')->assertMissing($user->document_photo_path);
        Storage::disk('public')->assertMissing($user->licence_photo_path);
    }

    public function test_sending_the_papers_again_removes_the_old_ones(): void
    {
        Storage::fake('local');

        $user = User::factory()->pendingReview()->create();

        $this->actingAs($user)->post(route('profile.documents'), [
            'document_photo' => UploadedFile::fake()->image('dni.jpg'),
            'licence_photo' => UploadedFile::fake()->image('carne.jpg'),
        ]);

        $first = $user->refresh()->document_photo_path;

        $this->actingAs($user)->post(route('profile.documents'), [
            'document_photo' => UploadedFile::fake()->image('dni-nuevo.jpg'),
            'licence_photo' => UploadedFile::fake()->image('carne-nuevo.jpg'),
        ]);

        // Una foto de un DNI que ya no se usa sigue siendo la foto de un DNI.
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($user->refresh()->document_photo_path);
    }

    public function test_sending_the_papers_does_not_verify_the_account_by_itself(): void
    {
        Storage::fake('local');

        $user = User::factory()->pendingReview()->create();

        $this->actingAs($user)->post(route('profile.documents'), [
            'document_photo' => UploadedFile::fake()->image('dni.jpg'),
            'licence_photo' => UploadedFile::fake()->image('carne.jpg'),
        ]);

        $this->assertFalse($user->refresh()->isVerified());
        $this->assertSame('pending', $user->verificationState());
    }

    public function test_a_text_file_is_not_a_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->pendingReview()->create();

        $this->actingAs($user)->post(route('profile.documents'), [
            'document_photo' => UploadedFile::fake()->create('dni.exe', 20),
            'licence_photo' => UploadedFile::fake()->image('carne.jpg'),
        ])->assertSessionHasErrors('document_photo');
    }

    public function test_changing_the_password_needs_the_old_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'meLaInvento',
            'password' => 'otraNueva123',
            'password_confirmation' => 'otraNueva123',
        ])->assertSessionHasErrors(['current_password' => 'Esa no es tu contraseña de ahora.']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_someone_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'otraNueva123',
            'password_confirmation' => 'otraNueva123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('otraNueva123', $user->refresh()->password));
    }

    public function test_the_command_verifies_an_account_that_sent_its_papers(): void
    {
        $user = User::factory()->pendingReview()->create([
            'email' => 'diego@aparcado.test',
            'document_photo_path' => 'documents/dni.jpg',
            'licence_photo_path' => 'licences/carne.jpg',
        ]);

        $this->artisan('aparcado:verify', ['email' => 'diego@aparcado.test'])
            ->assertSuccessful();

        // `verified_at` no es asignable en masa a propósito, y un `update()` con un
        // campo así se lo salta sin fallar: este test es lo que lo cazaría.
        $this->assertTrue($user->refresh()->isVerified());
    }

    public function test_the_command_refuses_an_account_without_papers(): void
    {
        User::factory()->pendingReview()->create(['email' => 'diego@aparcado.test']);

        $this->artisan('aparcado:verify', ['email' => 'diego@aparcado.test'])
            ->assertFailed();
    }

    public function test_the_command_can_take_the_verification_back(): void
    {
        $user = User::factory()->create([
            'email' => 'diego@aparcado.test',
            'verified_at' => Carbon::yesterday(),
        ]);

        $this->artisan('aparcado:verify', ['email' => 'diego@aparcado.test', '--undo' => true])
            ->assertSuccessful();

        $this->assertFalse($user->refresh()->isVerified());
    }

    public function test_the_command_says_so_when_the_email_is_unknown(): void
    {
        $this->artisan('aparcado:verify', ['email' => 'nadie@aparcado.test'])
            ->assertFailed();
    }
}
