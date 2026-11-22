<?php

declare(strict_types=1);

namespace Cine\Controller;

use Cine\App;
use Cine\Database\DbErrorTranslator;
use Cine\Http\Request;
use Cine\Http\Response;
use PDOException;

/**
 * Seat selection, purchase and the customer's tickets.
 */
final class BookingController
{
    public const MAX_SEATS = 10;

    public function __construct(private readonly App $app)
    {
    }

    /** @param array<string, string> $params */
    public function seats(Request $request, array $params): Response
    {
        $showtimeId = (int) $params['id'];
        $showtime = $this->app->showtimes()->find($showtimeId);
        if ($showtime === null) {
            return $this->app->error(404, 'Función no encontrada', 'La función que buscas no existe.');
        }

        $auth = $this->app->auth();
        $userId = $auth->id();
        $rows = [];
        foreach ($this->app->showtimes()->seatMap($showtimeId) as $seat) {
            $rows[$seat['row_label']][] = $seat;
        }

        return $this->app->page('booking/seats', [
            'pageTitle' => 'Elige tus sillas · ' . $showtime['movie_title'],
            'showtime' => $showtime,
            'rows' => $rows,
            'isOpen' => strtotime((string) $showtime['starts_at']) > time(),
            'unitPrice' => $this->app->showtimes()->priceFor($showtimeId, $auth->check() ? $userId : null),
            'paymentMethods' => $this->app->purchases()->paymentMethods(),
            'maxSeats' => self::MAX_SEATS,
            'canBuy' => $auth->can('tickets.buy'),
        ]);
    }

    /** @param array<string, string> $params */
    public function purchase(Request $request, array $params): Response
    {
        $showtimeId = (int) $params['id'];
        $back = '/funciones/' . $showtimeId;
        $auth = $this->app->auth();

        if (!$auth->check()) {
            return $this->app->redirect('/ingresar?next=' . rawurlencode($back), 'info', 'Ingresa para completar tu compra.');
        }
        if (!$auth->can('tickets.buy')) {
            return $this->app->error(403, 'Acceso restringido', 'Tu rol no permite comprar boletos.');
        }

        $seatIds = $request->intList('seats');
        $method = $request->input('payment_method_id');
        if ($seatIds === []) {
            return $this->app->redirect($back, 'error', 'Selecciona al menos una silla.');
        }
        if (count($seatIds) > self::MAX_SEATS) {
            return $this->app->redirect($back, 'error', 'Puedes comprar máximo ' . self::MAX_SEATS . ' sillas por compra.');
        }
        if (!Request::isId($method, 32767)) {
            return $this->app->redirect($back, 'error', 'Elige un medio de pago.');
        }

        try {
            $purchaseId = $this->app->purchases()->purchase((int) $auth->id(), $showtimeId, $seatIds, (int) $method);
        } catch (PDOException $e) {
            if (!DbErrorTranslator::isUserError($e)) {
                throw $e;
            }

            return $this->app->redirect($back, 'error', DbErrorTranslator::message($e));
        }

        return $this->app->redirect('/compras/' . $purchaseId, 'success', '¡Compra confirmada! Disfruta la función.');
    }

    /** @param array<string, string> $params */
    public function receipt(Request $request, array $params): Response
    {
        $auth = $this->app->auth();
        if (!$auth->check()) {
            return $this->app->redirect('/ingresar?next=' . rawurlencode($request->path));
        }
        $purchase = $this->app->purchases()->findForUser((int) $params['id'], (int) $auth->id());
        if ($purchase === null) {
            return $this->app->error(404, 'Compra no encontrada', 'No encontramos esa compra en tu cuenta.');
        }

        return $this->app->page('booking/receipt', ['pageTitle' => 'Compra #' . $purchase['purchase_id'], 'purchase' => $purchase]);
    }

    /** @param array<string, string> $params */
    public function myTickets(Request $request, array $params): Response
    {
        $auth = $this->app->auth();
        if (!$auth->check()) {
            return $this->app->redirect('/ingresar?next=/mis-boletos');
        }

        return $this->app->page('booking/my-tickets', [
            'pageTitle' => 'Mis boletos',
            'purchases' => $this->app->purchases()->forUser((int) $auth->id()),
        ]);
    }
}
