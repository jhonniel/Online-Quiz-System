<?php

namespace App\Support;

use App\Models\Setting;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class DocumentExportPdfBranding
{
    /**
     * @return array{system_name: string, contact_address: string, system_logo_data_uri: ?string}
     */
    public static function forPdf(): array
    {
        return [
            'system_name' => (string) Setting::get('system_name', config('app.name', 'System')),
            'contact_address' => (string) Setting::get('contact_address', ''),
            'system_logo_data_uri' => self::systemLogoDataUri(),
        ];
    }

    /**
     * PNG data URI for DomPDF (SVG data URIs are not rendered reliably).
     */
    public static function qrCodeDataUri(string $url, int $size = 96): string
    {
        if (extension_loaded('gd')) {
            $pngDataUri = self::qrCodePngDataUri($url, $size);
            if ($pngDataUri !== '') {
                return $pngDataUri;
            }
        }

        return '';
    }

    public static function qrCodeSvg(string $url, int $size = 96): string
    {
        try {
            return (string) QrCode::size($size)->format('svg')->margin(1)->generate($url);
        } catch (\Throwable) {
            return '';
        }
    }

    private static function qrCodePngDataUri(string $url, int $size = 96, int $margin = 1): string
    {
        try {
            $qrCode = Encoder::encode($url, ErrorCorrectionLevel::L());
            $matrix = $qrCode->getMatrix();
            $matrixSize = $matrix->getWidth();
            $moduleCount = $matrixSize + ($margin * 2);
            $moduleSize = max(1, (int) floor($size / $moduleCount));
            $imageSize = $moduleCount * $moduleSize;

            $image = imagecreatetruecolor($imageSize, $imageSize);
            if ($image === false) {
                return '';
            }

            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            imagefilledrectangle($image, 0, 0, $imageSize - 1, $imageSize - 1, $white);

            for ($y = 0; $y < $matrixSize; $y++) {
                for ($x = 0; $x < $matrixSize; $x++) {
                    if (! $matrix->get($x, $y)) {
                        continue;
                    }

                    $left = ($x + $margin) * $moduleSize;
                    $top = ($y + $margin) * $moduleSize;
                    imagefilledrectangle(
                        $image,
                        $left,
                        $top,
                        $left + $moduleSize - 1,
                        $top + $moduleSize - 1,
                        $black
                    );
                }
            }

            ob_start();
            imagepng($image);
            $png = ob_get_clean();
            imagedestroy($image);

            if (! is_string($png) || $png === '') {
                return '';
            }

            return 'data:image/png;base64,'.base64_encode($png);
        } catch (\Throwable) {
            return '';
        }
    }

    private static function systemLogoDataUri(): ?string
    {
        $path = Setting::get('system_logo');
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        foreach (['digitalocean', 'public', config('filesystems.default', 'local')] as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if (! $disk->exists($path)) {
                    continue;
                }

                $contents = $disk->get($path);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if ($extension === 'svg') {
                    return 'data:image/svg+xml;base64,'.base64_encode($contents);
                }

                $mime = match ($extension) {
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };

                return 'data:'.$mime.';base64,'.base64_encode($contents);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
