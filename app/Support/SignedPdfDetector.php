<?php

namespace App\Support;

final class SignedPdfDetector
{
    /**
     * Best-effort check for a signed PDF (ink annotation, e-sign field, scan, or other changes).
     */
    public function appearsSigned(string $uploadedPdfPath, ?string $unsignedPdfBinary = null): bool
    {
        $uploaded = @file_get_contents($uploadedPdfPath);
        if (! is_string($uploaded) || $uploaded === '') {
            return false;
        }

        if (! str_starts_with($uploaded, '%PDF-')) {
            return false;
        }

        if ($unsignedPdfBinary !== null && $unsignedPdfBinary !== '') {
            if (hash('sha256', $uploaded) === hash('sha256', $unsignedPdfBinary)) {
                return false;
            }

            if ($this->normalizedPdfFingerprint($uploaded) === $this->normalizedPdfFingerprint($unsignedPdfBinary)) {
                return false;
            }
        }

        if ($this->hasSignatureMarkers($uploaded)) {
            return true;
        }

        if ($unsignedPdfBinary !== null && $unsignedPdfBinary !== '') {
            return $this->hasMeaningfulSignatureDelta($uploaded, $unsignedPdfBinary);
        }

        return false;
    }

    private function hasSignatureMarkers(string $content): bool
    {
        $patterns = [
            '/\/Subtype\s*\/Ink\b/i',
            '/\/Subtype\s*\/Stamp\b/i',
            '/\/FT\s*\/Sig\b/i',
            '/\/Type\s*\/Sig\b/i',
            '/\/SigFlags\b/i',
            '/\/ByteRange\s*\[/i',
            '/\/Adobe\.PPKLite\b/i',
            '/\/adbe\.pkcs7/i',
            '/\/M\s*\([^)]+\)\s*\/D\s*\[/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }

    private function hasMeaningfulSignatureDelta(string $uploaded, string $unsigned): bool
    {
        if ($this->countPattern($uploaded, '/\/Subtype\s*\/Image\b/i') > $this->countPattern($unsigned, '/\/Subtype\s*\/Image\b/i')) {
            return true;
        }

        if ($this->countPattern($uploaded, '/\/Type\s*\/Annot\b/i') > $this->countPattern($unsigned, '/\/Type\s*\/Annot\b/i')) {
            return true;
        }

        if ($this->countPattern($uploaded, '/\bstream\r?\n/i') > $this->countPattern($unsigned, '/\bstream\r?\n/i')) {
            $sizeDelta = strlen($uploaded) - strlen($unsigned);

            return $sizeDelta >= 400;
        }

        $sizeDelta = strlen($uploaded) - strlen($unsigned);
        if ($sizeDelta >= 1500) {
            return true;
        }

        return false;
    }

    private function countPattern(string $content, string $pattern): int
    {
        return preg_match_all($pattern, $content) ?: 0;
    }

    /**
     * Ignore PDF metadata that changes on every save (CreationDate, ModDate, ID).
     */
    private function normalizedPdfFingerprint(string $content): string
    {
        $normalized = preg_replace('/\/CreationDate\s*\([^)]*\)/i', '', $content) ?? $content;
        $normalized = preg_replace('/\/ModDate\s*\([^)]*\)/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\/ID\s*\[[^\]]*\]/i', '', $normalized) ?? $normalized;

        return hash('sha256', $normalized);
    }
}
