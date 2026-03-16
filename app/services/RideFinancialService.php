<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\DriverBalance;
use App\Models\DriverBalanceTransaction;
use Illuminate\Support\Facades\DB;

class RideFinancialService
{
    public function settleCompletedRide(Ride $ride): void
    {
        if (!$ride->driver_id) {
            throw new \RuntimeException('A corrida não possui motorista vinculado.');
        }

        if ($ride->payment_status !== 'paid') {
            throw new \RuntimeException('A corrida precisa estar paga para liquidar financeiramente.');
        }

        DB::transaction(function () use ($ride) {
            $balance = DriverBalance::firstOrCreate(
                ['driver_id' => $ride->driver_id],
                [
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'amount_owed_to_platform' => 0,
                ]
            );

            $alreadyProcessed = DriverBalanceTransaction::where('ride_id', $ride->id)
                ->whereIn('type', [
                    'ride_cash_commission_due',
                    'ride_driver_credit',
                ])
                ->exists();

            if ($alreadyProcessed) {
                return;
            }

            $fareTotal = (float) ($ride->fare_total ?? $ride->fare ?? 0);
            $platformFee = (float) ($ride->platform_fee ?? 0);
            $gatewayFee = (float) ($ride->gateway_fee ?? 0);
            $driverNet = (float) ($ride->driver_net_amount ?? 0);

            if ($ride->payment_method === 'cash') {
                $balance->increment('amount_owed_to_platform', $platformFee);

                DriverBalanceTransaction::create([
                    'driver_id' => $ride->driver_id,
                    'ride_id' => $ride->id,
                    'type' => 'ride_cash_commission_due',
                    'direction' => 'debit',
                    'amount' => $platformFee,
                    'status' => 'posted',
                    'description' => 'Comissão devida à plataforma por corrida paga em dinheiro.',
                    'meta' => [
                        'fare_total' => $fareTotal,
                        'platform_fee' => $platformFee,
                        'gateway_fee' => $gatewayFee,
                        'driver_net_amount' => $driverNet,
                        'payment_method' => $ride->payment_method,
                    ],
                ]);

                return;
            }

            if (in_array($ride->payment_method, ['pix_platform', 'card'])) {
                $balance->increment('available_balance', $driverNet);

                DriverBalanceTransaction::create([
                    'driver_id' => $ride->driver_id,
                    'ride_id' => $ride->id,
                    'type' => 'ride_driver_credit',
                    'direction' => 'credit',
                    'amount' => $driverNet,
                    'status' => 'posted',
                    'description' => 'Crédito líquido do motorista por corrida paga pela plataforma.',
                    'meta' => [
                        'fare_total' => $fareTotal,
                        'platform_fee' => $platformFee,
                        'gateway_fee' => $gatewayFee,
                        'driver_net_amount' => $driverNet,
                        'payment_method' => $ride->payment_method,
                    ],
                ]);

                return;
            }

            throw new \RuntimeException('Forma de pagamento não suportada para liquidação financeira.');
        });
    }

    public function calculateRideFinancials(
        float $fareTotal,
        string $paymentMethod,
        float $commissionPercent = 10.0,
        float $gatewayFee = 0.0
    ): array {
        $platformFee = round($fareTotal * ($commissionPercent / 100), 2);

        $driverNet = round($fareTotal - $platformFee - $gatewayFee, 2);

        if ($driverNet < 0) {
            $driverNet = 0;
        }

        return [
            'fare_total' => round($fareTotal, 2),
            'platform_fee' => $platformFee,
            'gateway_fee' => round($gatewayFee, 2),
            'driver_net_amount' => $driverNet,
            'payment_method' => $paymentMethod,
        ];
    }
}