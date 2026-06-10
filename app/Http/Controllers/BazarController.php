<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BazarController extends Controller
{
    /**
     * Muestra la vitrina del Bazar Animal y el Dashboard del Club de Huellas.
     */
    public function showBazarHub(): Response
    {
        $user = Auth::user();
        $points = $user ? $user->points : 0;

        // Catálogo del Bazar (Emprendimientos Locales)
        $products = [
            [
                'id' => 1,
                'title' => 'Pastelería Canina Saborizada Natural',
                'category' => 'reposteria',
                'price' => 8500,
                'store_name' => 'Canela & Hueso',
                'is_solidary' => true, // Sello Emprendimiento Solidario (dona %)
                'image_placeholder' => '🍰',
            ],
            [
                'id' => 2,
                'title' => 'Correas de Nylon Doble Costura Reforzadas',
                'category' => 'accesorios',
                'price' => 12000,
                'store_name' => 'Raza Fuerte',
                'is_solidary' => false,
                'image_placeholder' => '🦮',
            ],
            [
                'id' => 3,
                'title' => 'Ropa de Abrigo Polar Acolchada para Invierno',
                'category' => 'accesorios',
                'price' => 6990,
                'store_name' => 'Patitas Abrigadas',
                'is_solidary' => true,
                'image_placeholder' => '🧥',
            ],
        ];

        // Catálogo de Premios Canjeables en el Club de Huellas
        $rewards = [
            [
                'id' => 'rew_1',
                'title' => '15% de Descuento en Alimentos Premium',
                'cost' => 150, // Puntos requeridos (Huellas)
                'sponsor' => 'SuperPet Shop',
                'description' => 'Aplica para compras presenciales o web en sacos de > 10kg.',
            ],
            [
                'id' => 'rew_2',
                'title' => 'Baño Clínico e Higienización Completa Gratis',
                'cost' => 300,
                'sponsor' => 'Estética Veterinaria Huellitas',
                'description' => 'Corte de uñas, baño sanitario y secado.',
            ],
            [
                'id' => 'rew_3',
                'title' => 'Juguete Interactivo Porta Snack KONG',
                'cost' => 100,
                'sponsor' => 'Alianzas Solidarias',
                'description' => 'Juguete de goma ultra resistente para la ansiedad.',
            ],
        ];

        return Inertia::render('Bazar/Hub', [
            'products' => $products,
            'rewards' => $rewards,
            'points' => $points,
        ]);
    }

    /**
     * Procesa el canje de premios del Club de Huellas.
     */
    public function claimReward(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'reward_id' => ['required', 'string'],
        ]);

        // Catálogo oficial con costos inalterables del lado del servidor
        $rewardsCatalog = [
            'rew_1' => 150, // 15% Descuento
            'rew_2' => 300, // Baño Clínico Gratis
            'rew_3' => 100, // Juguete Porta Snack KONG
        ];

        if (!array_key_exists($request->reward_id, $rewardsCatalog)) {
            return back()->withErrors(['reward_id' => 'Premio no válido o inexistente en el catálogo del bazar.']);
        }

        $cost = $rewardsCatalog[$request->reward_id];

        if ($user->points < $cost) {
            return back()->withErrors(['points' => 'No tienes suficientes Huellas acumuladas para canjear este premio.']);
        }

        // Descontar puntos
        $oldPoints = $user->points;
        $user->decrement('points', $cost);

        AuditService::log('claim_reward_points', $user, ['points' => $oldPoints], ['points' => $user->points]);

        // Generar un código de cupón aleatorio
        $voucherCode = strtoupper(substr(md5(uniqid()), 0, 8));

        return back()->with('success', '¡Canje exitoso! Tu código de cupón es: ' . $voucherCode . '. Se han descontado ' . $cost . ' Huellas de tu cuenta.');
    }

    /**
     * Simulación de pago y suscripciones Premium en el Bazar mediante MercadoPago.
     */
    public function purchasePremiumPlan(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'plan_name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
        ]);

        // Simulación de interacción con la API de MercadoPago para SiteGround:
        // En una integración real, instanciaríamos el SDK de MercadoPago y crearíamos una Preference:
        //
        // SDK::setAccessToken(config('services.mercadopago.token'));
        // $preference = new Preference();
        // $item = new Item();
        // $item->title = $request->plan_name;
        // $item->quantity = 1;
        // $item->unit_price = $request->price;
        // $preference->items = [$item];
        // $preference->save();
        //
        // $initPoint = $preference->init_point;
        
        // Simulamos la generación del checkout_url retornado por la pasarela de MercadoPago
        $sandboxCheckoutUrl = "https://www.mercadopago.cl/sandbox/checkout/preference?pref_id=mp_" . md5($user->id . time());

        // Auditamos el inicio de pago
        AuditService::log('initiate_mercadopago_checkout', $user, null, [
            'plan' => $request->plan_name,
            'price' => $request->price,
            'url' => $sandboxCheckoutUrl,
        ]);

        // Redireccionamos a la pantalla externa de MercadoPago (en producción sería un redirect externo)
        // Para propósitos locales de demostración, devolvemos un flash para simular el click
        return back()->with('checkout_url', $sandboxCheckoutUrl)->with('success', 'Plan Premium iniciado. Redirigiendo a pasarela segura de MercadoPago...');
    }
}
