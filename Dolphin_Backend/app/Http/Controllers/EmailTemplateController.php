<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    /**
     * Return the rendered lead registration template as HTML.
     * Query params: registration_link, name
     */
    public function leadRegistration(Request $request)
    {
        $registrationUrl = $request->query('registration_link') ?: $request->input('registration_link');
        $name = $request->query('name') ?: $request->input('name');

        // Render the Blade view to HTML and return it as plain text
        $html = view('emails.lead_registration', [
            'registrationUrl' => $registrationUrl,
            'name' => $name,
            'body' => null,
        ])->render();

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
