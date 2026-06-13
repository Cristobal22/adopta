<?php

namespace App\Services;

use App\Models\Adoption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    /**
     * Decodifica y guarda la firma desde el dibujo de Canvas en almacenamiento privado.
     */
    public function saveSignatureImage(string $base64Data, int $adoptionId): string
    {
        $signatureImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
        $signaturePath = 'signatures/signature_' . $adoptionId . '_' . time() . '.png';
        Storage::disk('local')->put('private/' . $signaturePath, $signatureImage);

        return $signaturePath;
    }

    /**
     * Genera el contrato firmado en HTML Legal Certificado con metadatos.
     */
    public function generateSignedContract(Adoption $adoption, string $signaturePath, string $ipAddress): string
    {
        $adoption->load(['pet', 'adopter', 'rescuer']);
        
        $contractHtml = view('contracts.signed', [
            'adoption' => $adoption,
            'signature_path' => storage_path('app/private/' . $signaturePath),
            'ip_address' => $ipAddress,
            'signed_at' => now()->toDateTimeString(),
        ])->render();

        $contractPath = 'contracts/contract_' . $adoption->id . '_' . time() . '.html';
        Storage::disk('local')->put('private/' . $contractPath, $contractHtml);

        return $contractPath;
    }

    /**
     * Almacena el PDF del contrato firmado físicamente.
     */
    public function saveUploadedContract(UploadedFile $file, int $adoptionId): string
    {
        $contractPath = $file->storeAs(
            'private/contracts',
            'uploaded_contract_' . $adoptionId . '_' . time() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        return $contractPath;
    }
}
