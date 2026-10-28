<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faqs = [
            'shipping' => [
                'en' => [
                    'question' => 'Where is my order, and how long does shipping take?',
                    'answer' => 'Standard shipping takes 3–5 business days. Once your order ships, we’ll email you a tracking link. If you need help locating a package, share your order number with our support team.',
                ],
                'es' => [
                    'question' => '¿Dónde está mi pedido y cuánto tarda el envío?',
                    'answer' => 'El envío estándar tarda entre 3 y 5 días laborables. Cuando salga tu pedido, te enviaremos un enlace de seguimiento por correo electrónico. Si necesitas ayuda para localizar un paquete, comparte tu número de pedido con nuestro equipo de atención.',
                ],
            ],
            'returns' => [
                'en' => [
                    'question' => 'What is your return policy?',
                    'answer' => 'You can return unused items within 30 days of delivery. Start a return by contacting our support team with your order number; we’ll send you the next steps.',
                ],
                'es' => [
                    'question' => '¿Cuál es vuestra política de devoluciones?',
                    'answer' => 'Puedes devolver los artículos sin usar durante los 30 días posteriores a la entrega. Para iniciar una devolución, contacta con nuestro equipo de atención e indica tu número de pedido; te enviaremos los siguientes pasos.',
                ],
            ],
            'hours' => [
                'en' => [
                    'question' => 'What are your support hours?',
                    'answer' => 'Our Harbor Desk support team is available Monday through Friday, 9:00 a.m.–6:00 p.m. Eastern Time. We’re closed on major US holidays.',
                ],
                'es' => [
                    'question' => '¿Cuál es vuestro horario de atención?',
                    'answer' => 'El equipo de atención de Harbor Desk está disponible de lunes a viernes, de 9:00 a 18:00 (hora del este de EE. UU.). Cerramos los principales festivos estadounidenses.',
                ],
            ],
            'contact' => [
                'en' => [
                    'question' => 'How can I contact customer support?',
                    'answer' => 'Email our Harbor Desk team at support@harbordesk.example. Include your order number if your question is about a purchase, and we’ll get back to you during support hours.',
                ],
                'es' => [
                    'question' => '¿Cómo puedo contactar con atención al cliente?',
                    'answer' => 'Escribe al equipo de Harbor Desk a support@harbordesk.example. Si tu consulta es sobre una compra, incluye el número de pedido y te responderemos durante el horario de atención.',
                ],
            ],
        ];

        foreach ($faqs as $slug => $translations) {
            foreach ($translations as $locale => $faq) {
                FaqItem::query()->updateOrCreate(
                    ['slug' => $slug, 'locale' => $locale],
                    $faq,
                );
            }
        }
    }
}
