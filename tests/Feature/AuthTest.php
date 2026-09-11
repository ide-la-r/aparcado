<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_the_register_form_opens(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
    }

    public function test_someone_can_open_an_account(): void
    {
        $response = $this->post(route('register'), $this->validData());

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::query()->where('email', 'ismael@aparcado.test')->firstOrFail();

        $this->assertSame('Ismael De la Rosa', $user->fullName());
        $this->assertSame('1995-03-14', $user->birthdate->toDateString());
    }

    public function test_the_password_is_never_stored_in_clear(): void
    {
        $this->post(route('register'), $this->validData(['password' => 'unaContraseña8', 'password_confirmation' => 'unaContraseña8']));

        $user = User::query()->where('email', 'ismael@aparcado.test')->firstOrFail();

        $this->assertNotSame('unaContraseña8', $user->password);
        $this->assertTrue(Hash::check('unaContraseña8', $user->password));
    }

    public function test_a_new_account_still_has_to_be_verified(): void
    {
        $this->post(route('register'), $this->validData());

        $user = User::query()->where('email', 'ismael@aparcado.test')->firstOrFail();

        // Puede entrar y mirar, pero no publicar ni reservar hasta que alguien mire
        // el DNI y el carné.
        $this->assertFalse($user->isVerified());
        $this->assertTrue($user->active);
    }

    public function test_someone_too_young_cannot_open_an_account(): void
    {
        $sixteen = Carbon::today()->subYears(16)->toDateString();

        $this->post(route('register'), $this->validData(['birthdate' => $sixteen]))
            ->assertSessionHasErrors(['birthdate' => 'Hay que tener 18 años o más.']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_two_passwords_have_to_match(): void
    {
        $this->post(route('register'), $this->validData(['password_confirmation' => 'otraCosa123']))
            ->assertSessionHasErrors(['password' => 'Las dos contraseñas no coinciden.']);

        $this->assertGuest();
    }

    public function test_the_same_document_cannot_be_used_twice(): void
    {
        User::factory()->create(['document_number' => '12345678Z']);

        $this->post(route('register'), $this->validData(['document_number' => '12345678Z']))
            ->assertSessionHasErrors(['document_number' => 'Ya hay una cuenta con ese documento.']);
    }

    public function test_someone_can_come_back_in(): void
    {
        $user = User::factory()->create(['email' => 'lucia@aparcado.test']);

        $this->post(route('login'), [
            'email' => 'lucia@aparcado.test',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_says_the_same_as_an_unknown_email(): void
    {
        User::factory()->create(['email' => 'lucia@aparcado.test']);

        $wrongPassword = $this->post(route('login'), [
            'email' => 'lucia@aparcado.test',
            'password' => 'meLaInvento',
        ]);

        $unknownEmail = $this->post(route('login'), [
            'email' => 'nadie@aparcado.test',
            'password' => 'password',
        ]);

        // Si el mensaje fuera distinto, cualquiera podría averiguar quién tiene
        // cuenta aquí probando correos.
        $message = 'El correo o la contraseña no son correctos.';
        $wrongPassword->assertSessionHasErrors(['email' => $message]);
        $unknownEmail->assertSessionHasErrors(['email' => $message]);
        $this->assertGuest();
    }

    public function test_it_stops_after_six_failed_attempts(): void
    {
        User::factory()->create(['email' => 'lucia@aparcado.test']);

        foreach (range(1, 6) as $attempt) {
            $this->post(route('login'), ['email' => 'lucia@aparcado.test', 'password' => 'mal']);
        }

        $this->post(route('login'), ['email' => 'lucia@aparcado.test', 'password' => 'mal'])
            ->assertSessionHasErrorsIn('default', ['email']);

        $this->assertStringContainsString(
            'Demasiados intentos',
            session('errors')->first('email'),
        );
    }

    public function test_getting_in_right_clears_the_counter(): void
    {
        User::factory()->create(['email' => 'lucia@aparcado.test']);

        foreach (range(1, 3) as $attempt) {
            $this->post(route('login'), ['email' => 'lucia@aparcado.test', 'password' => 'mal']);
        }

        $this->post(route('login'), ['email' => 'lucia@aparcado.test', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->post(route('logout'));

        // Y los tres fallos de antes ya no cuentan.
        $this->post(route('login'), ['email' => 'lucia@aparcado.test', 'password' => 'password'])
            ->assertRedirect(route('home'));
    }

    public function test_someone_can_leave(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_the_header_says_who_you_are(): void
    {
        $user = User::factory()->create(['name' => 'Ismael']);

        $this->get(route('home'))->assertOk()->assertSee('Entrar')->assertSee('Crear cuenta');

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Hola, Ismael')
            ->assertSee('Salir')
            ->assertDontSee('Crear cuenta');
    }

    public function test_someone_already_in_does_not_see_the_forms_again(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('login'))->assertRedirect(route('home'));
        $this->get(route('register'))->assertRedirect(route('home'));
    }

    private function validData(array $overrides = []): array
    {
        return [
            'name' => 'Ismael',
            'surname' => 'De la Rosa',
            'email' => 'ismael@aparcado.test',
            'phone' => '600112233',
            'birthdate' => '1995-03-14',
            'document_type' => 'DNI',
            'document_number' => '99887766A',
            'password' => 'unaContraseña8',
            'password_confirmation' => 'unaContraseña8',
            ...$overrides,
        ];
    }
}
