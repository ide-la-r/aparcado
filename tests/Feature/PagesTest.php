<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    public static function pages(): array
    {
        return [
            'sobre aparcado' => ['pages.about', 'Sobre Aparcado'],
            'ayuda' => ['pages.help', '¿Hace falta verificar la cuenta para todo?'],
            'contacto' => ['pages.contact', 'Contacto'],
            'aviso legal' => ['pages.legal', 'Aviso legal'],
            'privacidad' => ['pages.privacy', 'Los papeles no se publican'],
            'cookies' => ['pages.cookies', 'No hay banner, y es a propósito'],
            'créditos' => ['pages.credits', 'Créditos de las fotos'],
        ];
    }

    /**
     * Las fotos de ejemplo salen de Wikimedia y casi todas son CC BY-SA, que obliga
     * a citar a quien las hizo. Si esta página se queda sin nombrar a alguno, el
     * incumplimiento no se ve por ningún lado: no falla nada, simplemente falta el
     * crédito.
     */
    public function test_the_credits_page_names_every_author(): void
    {
        $response = $this->get(route('pages.credits'))->assertOk();

        foreach (config('demo_photos') as $photo) {
            $response->assertSee($photo['author'], escape: false)
                ->assertSee($photo['licence'])
                ->assertSee($photo['page'], escape: false);
        }
    }

    #[DataProvider('pages')]
    public function test_the_page_opens(string $route, string $text): void
    {
        $this->get(route($route))->assertOk()->assertSee($text);
    }

    public function test_every_page_is_linked_from_the_footer(): void
    {
        $response = $this->get(route('home'))->assertOk();

        // Una página legal a la que no se llega desde ninguna parte es como no
        // tenerla.
        foreach (array_keys(self::pages()) as $name) {
            [$route] = self::pages()[$name];
            $response->assertSee(route($route));
        }
    }

    public function test_the_legal_pages_say_that_this_is_not_a_real_service(): void
    {
        // Es lo más importante que tienen que decir, y por eso tiene un test: es un
        // proyecto personal, no hay empresa detrás y no se mueve dinero de verdad.
        $this->get(route('pages.legal'))->assertOk()->assertSee('no se mueve dinero de verdad');
        $this->get(route('pages.about'))->assertOk()->assertSee('No es un servicio real de alquiler');
    }

    public function test_a_page_that_does_not_exist_says_so_with_our_own_page(): void
    {
        $this->get('/esto-no-existe')
            ->assertNotFound()
            ->assertSee('Aquí no hay nada')
            ->assertSee('404');
    }

    public function test_the_error_pages_do_not_touch_the_database(): void
    {
        $user = User::factory()->create();

        /*
         * La cabecera normal pinta el contador de mensajes sin leer, y eso es una
         * consulta. Una página de error 500 que consulta la base de datos revienta
         * justo cuando el motivo del error es que la base de datos no contesta.
         *
         * Por eso las páginas de error usan un armazón aparte, y esto lo comprueba:
         * ni con la sesión abierta aparece el menú de la cabecera.
         */
        foreach (['403', '404', '419', '429', '500', '503'] as $code) {
            $html = $this->actingAs($user)->blade("<x-error-page code=\"{$code}\" title=\"Vaya\" text=\"Algo\" />");

            $this->assertStringNotContainsString(route('messages.index'), $html, "La página {$code} pinta la cabecera.");
            $this->assertStringNotContainsString('Mis reservas', $html);
            $this->assertStringContainsString($code, $html);
        }
    }

    public function test_the_error_pages_always_offer_a_way_out(): void
    {
        foreach (['403', '404', '500', '503'] as $code) {
            $html = view("errors.{$code}")->render();

            $this->assertStringContainsString('Ir a la portada', $html);
            $this->assertStringContainsString('/coches', $html);
        }
    }
}
