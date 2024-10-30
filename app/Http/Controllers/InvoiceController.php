<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{

    public function index() {
        $invoices = Invoice::all();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        try {
            $request->validate(['xml' => 'required|file|mimes:xml']);
            $xml = simplexml_load_file($request->file('xml'), null, LIBXML_NOCDATA);
            if (!$xml) {
                throw new \Exception('Error al cargar el archivo XML.');
            }

            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
            $xml->registerXPathNamespace('tfd', $namespaces['tfd']);

            $cfdi = $this->getNode($xml, '//cfdi:Comprobante');
            $emisor = $this->getNode($xml, '//cfdi:Emisor');
            $receptor = $this->getNode($xml, '//cfdi:Receptor');
            $tfd = $this->getNode($xml, '//tfd:TimbreFiscalDigital');


            if (!$cfdi || !$tfd || !$emisor || !$receptor) {
                throw new \Exception('No se encontraron todos los datos necesarios en el XML.');
            }


            $uuid = (string) $tfd['UUID'];
            $folio = (string) $cfdi['Folio'] ?: substr($uuid, -8);
            $emisorNombre = (string) $emisor['Nombre'];
            $receptorNombre = (string) $receptor['Nombre'];
            $moneda = (string) $cfdi['Moneda'];
            $total = (float) $cfdi['Total'];
            $tipoCambio = $this->consultarTipoCambio();


            Invoice::create([
                'uuid' => $uuid,
                'folio' => $folio,
                'emisor' => $emisorNombre,
                'receptor' => $receptorNombre,
                'moneda' => $moneda,
                'total' => $total,
                'tipo_cambio' => $tipoCambio,
            ]);

            return response()->json(['message' => 'Factura registrada correctamente'], 201);
        } catch (ValidationException $e) {

            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {

            Log::error('Error al registrar la factura: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar el archivo XML'], 500);
        }
    }

    private function getNode($xml, string $query)
    {
        $result = $xml->xpath($query);
        return $result[0] ?? null;
    }

    private function consultarTipoCambio(): float
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->get('https://www.banxico.org.mx/tipcamb/tipCamMIAction.do');

            if ($response->successful()) {
                return (float) ($response->json()['dof']['tipo_cambio'] ?? 1.0);
            }

            throw new \Exception('No se pudo obtener el tipo de cambio');
        } catch (\Exception $e) {
            Log::warning('Error al consultar el tipo de cambio: ' . $e->getMessage());
            return 1.0;
        }
    }
}
