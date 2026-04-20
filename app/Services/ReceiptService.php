<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

class ReceiptService
{
    public function generateReceiptHtml(Order $order): string
    {
        $payments = $order->payments()->paid()->get();
        $paymentMethods = $payments->pluck('method')->implode(', ');

        $itemsHtml = '';
        foreach ($order->items as $item) {
            $itemsHtml .= sprintf(
                "<tr>
                    <td colspan='2'>%s</td>
                </tr>
                <tr>
                    <td style='text-align: right;'>x%d</td>
                    <td style='text-align: right;'>%s</td>
                </tr>",
                htmlspecialchars($item->product_name),
                $item->quantity,
                'Rp ' . number_format($item->subtotal, 0, ',', '.')
            );
        }

        return "
            <div style='font-family: monospace; width: 80mm; padding: 5mm;'>
                <div style='text-align: center; margin-bottom: 10px;'>
                    <h2 style='margin: 0;'>" . htmlspecialchars(session('tenant_name')) . "</h2>
                    <div style='border-top: 1px dashed #000; margin: 5px 0;'></div>
                </div>

                <table style='width: 100%; border-collapse: collapse; font-size: 12px;'>
                    <tr>
                        <td>Kasir:</td>
                        <td style='text-align: right;'>{$order->cashier->name}</td>
                    </tr>
                    <tr>
                        <td>Tanggal:</td>
                        <td style='text-align: right;'>{$order->created_at->format('d M Y, H:i')}</td>
                    </tr>
                    <tr>
                        <td>Tx #:</td>
                        <td style='text-align: right;'>{$order->id}</td>
                    </tr>
                </table>

                <div style='border-top: 1px dashed #000; margin: 10px 0;'></div>

                <table style='width: 100%; border-collapse: collapse; font-size: 12px;'>
                    {$itemsHtml}
                </table>

                <div style='border-top: 1px dashed #000; margin: 10px 0;'></div>

                <table style='width: 100%; border-collapse: collapse; font-size: 12px;'>
                    <tr>
                        <td>Subtotal:</td>
                        <td style='text-align: right;'>{$order->total_amount_formatted}</td>
                    </tr>";

        if ($order->discount_value > 0) {
            $discountLabel = $order->discount_type === 'percent'
                ? "Diskon ({$order->discount_value}%):"
                : 'Diskon:';

            $itemsHtml .= "
                    <tr>
                        <td>{$discountLabel}</td>
                        <td style='text-align: right;'>-Rp " . number_format($order->discount_value, 0, ',', '.') . "</td>
                    </tr>";
        }

        $itemsHtml .= "
                    <tr style='font-weight: bold; font-size: 14px;'>
                        <td>TOTAL:</td>
                        <td style='text-align: right;'>{$order->net_amount_formatted}</td>
                    </tr>
                </table>

                <div style='border-top: 1px solid #000; margin: 10px 0;'></div>

                <table style='width: 100%; border-collapse: collapse; font-size: 12px;'>
                    <tr>
                        <td>Bayar:</td>
                        <td style='text-align: right;'>" . ucfirst($paymentMethods) . "</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td style='text-align: right; font-weight: bold;'>LUNAS</td>
                    </tr>
                </table>

                <div style='border-top: 1px solid #000; margin: 10px 0;'></div>

                <div style='text-align: center; font-size: 11px; margin-top: 10px;'>
                    Terima kasih sudah berkunjung!
                </div>
            </div>
        ";
    }

    public function printToNetwork(Order $order, string $printerIp, int $port = 9100): void
    {
        try {
            $connector = new NetworkPrintConnector($printerIp, $port);
            $printer = new Printer($connector);

            $this->printToEscPos($printer, $order);

            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception("Failed to print to network printer: " . $e->getMessage());
        }
    }

    public function printToUsb(Order $order, string $devicePath): void
    {
        try {
            $connector = new FilePrintConnector($devicePath);
            $printer = new Printer($connector);

            $this->printToEscPos($printer, $order);

            $printer->close();
        } catch (\Exception $e) {
            throw new \Exception("Failed to print to USB printer: " . $e->getMessage());
        }
    }

    private function printToEscPos(Printer $printer, Order $order): void
    {
        $payments = $order->payments()->paid()->get();
        $paymentMethods = $payments->pluck('method')->implode(', ');

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text(session('tenant_name') . "\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat("=", 32) . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Kasir  : " . $order->cashier->name . "\n");
        $printer->text("Tanggal : " . $order->created_at->format('d M Y, H:i') . "\n");
        $printer->text("Tx #   : " . $order->id . "\n");

        $printer->text(str_repeat("-", 32) . "\n");

        foreach ($order->items as $item) {
            $printer->text($item->product_name . "\n");
            $printer->text(sprintf(
                "   x%d %' 20s\n",
                $item->quantity,
                'Rp ' . number_format($item->subtotal, 0, ',', '.')
            ));
        }

        $printer->text(str_repeat("-", 32) . "\n");

        $printer->text(sprintf("   %-20s %' 10s\n", "Subtotal", $order->total_amount_formatted));

        if ($order->discount_value > 0) {
            $discountLabel = $order->discount_type === 'percent'
                ? "Diskon ({$order->discount_value}%)"
                : 'Diskon';

            $printer->text(sprintf(
                "   %-20s %' 10s\n",
                $discountLabel,
                '-Rp ' . number_format($order->discount_value, 0, ',', '.')
            ));
        }

        $printer->setEmphasis(true);
        $printer->text(sprintf("   %-20s %' 10s\n", "TOTAL", $order->net_amount_formatted));
        $printer->setEmphasis(false);

        $printer->text(str_repeat("=", 32) . "\n");

        $printer->text("Bayar  : " . ucfirst($paymentMethods) . "\n");
        $printer->setEmphasis(true);
        $printer->text("Status : LUNAS\n");
        $printer->setEmphasis(false);

        $printer->text(str_repeat("=", 32) . "\n");

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Terima kasih sudah berkunjung!\n");

        $printer->feed(3);
    }
}
