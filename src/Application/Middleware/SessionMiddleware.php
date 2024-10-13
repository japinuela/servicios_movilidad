<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
    	// Log para verificar si el middleware se ejecuta
        error_log('SessionMiddleware ejecutado');
		
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            session_start();
            $request = $request->withAttribute('session', $_SESSION);
        }

        // Procesar la solicitud y obtener la respuesta del handler
        $response = $handler->handle($request);

		// Log para verificar si el middleware se ejecuta
        error_log('SessionMiddleware agregando CORS');
		
        // Agregar encabezados CORS a la respuesta
        $response = $response
			->withHeader('Access-Control-Allow-Origin', '*')  // Permitir cualquier origen
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, OPTIONS')
			->withHeader('Access-Control-Allow-Credentials', 'true')  // Permitir credenciales si es necesario (cookies, headers Authorization)
			->withHeader('Access-Control-Expose-Headers', 'Authorization, X-Total-Count')  // Exponer headers adicionales al frontend
			->withHeader('Access-Control-Max-Age', '3600');  // Tiempo en segundos que los navegadores pueden cachear la respuesta preflight
	
		// Log para verificar las cabeceras
		error_log('Cabeceras después de aplicar CORS: ' . print_r($response->getHeaders(), true));
		return $response;

    }
}
