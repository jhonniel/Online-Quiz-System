<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

final class EmployeePolicyMaterial
{
    public const SETTING_KEY = 'employee_policy_material_pdfs';

    /**
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function all(): array
    {
        return EmployeeDocumentMaterial::all('policy');
    }

    /**
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function available(): array
    {
        return EmployeeDocumentMaterial::available('policy');
    }

    /**
     * @return array{id: string, name: string, path: string, disk: string, sort: int}|null
     */
    public static function find(string $id): ?array
    {
        return EmployeeDocumentMaterial::find('policy', $id);
    }

    /**
     * @param  list<array{id?: string, name?: string, sort?: int}>  $nameUpdates
     * @param  list<string>  $removeIds
     * @param  list<array{name: string, pdf: \Illuminate\Http\UploadedFile}>  $newUploads
     */
    public static function syncFromAdminInput(array $nameUpdates, array $removeIds, array $newUploads): void
    {
        EmployeeDocumentMaterial::syncFromAdminInput('policy', $nameUpdates, $removeIds, $newUploads);
    }

    public static function streamResponseForId(string $id, string $disposition = 'inline'): Response
    {
        return EmployeeDocumentMaterial::streamResponseForId('policy', $id, $disposition);
    }
}
