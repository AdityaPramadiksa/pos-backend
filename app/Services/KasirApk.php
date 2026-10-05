<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * File APK aplikasi kasir yang dibagikan lewat halaman /unduh.
 * Disimpan di storage/app/apk (ikut volume Docker, tidak hilang saat deploy).
 */
class KasirApk
{
    private const FILE = 'kasir-men-gede.apk';
    private const INFO = 'info.json';

    private static function dir(): string
    {
        return storage_path('app/apk');
    }

    public static function path(): string
    {
        return self::dir() . DIRECTORY_SEPARATOR . self::FILE;
    }

    /**
     * Data APK yang sedang dibagikan, atau null bila belum ada.
     *
     * @return array{version: string, notes: ?string, size: int, uploaded_at: string, sha256: string}|null
     */
    public static function info(): ?array
    {
        $infoPath = self::dir() . DIRECTORY_SEPARATOR . self::INFO;
        if (!is_file(self::path()) || !is_file($infoPath)) {
            return null;
        }

        $info = json_decode((string) file_get_contents($infoPath), true);

        return is_array($info) ? $info : null;
    }

    /** true bila file berawalan tanda ZIP (APK adalah arsip ZIP) */
    public static function looksLikeApk(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $magic = $handle ? fread($handle, 4) : '';
        if ($handle) {
            fclose($handle);
        }

        return $magic === "PK\x03\x04";
    }

    /** Ganti APK yang dibagikan dengan versi baru */
    public static function store(UploadedFile $file, string $version, ?string $notes): array
    {
        if (!is_dir(self::dir())) {
            mkdir(self::dir(), 0775, true);
        }

        // Tulis ke file sementara dulu supaya unduhan yang sedang berjalan tidak rusak
        $temp = self::path() . '.baru';
        $file->move(self::dir(), basename($temp));
        rename($temp, self::path());

        $info = [
            'version' => $version,
            'notes' => $notes ? trim($notes) : null,
            'size' => filesize(self::path()),
            'uploaded_at' => now()->toIso8601String(),
            'sha256' => hash_file('sha256', self::path()),
        ];
        file_put_contents(
            self::dir() . DIRECTORY_SEPARATOR . self::INFO,
            json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $info;
    }

    public static function downloadName(array $info): string
    {
        return 'kasir-men-gede-' . preg_replace('/[^0-9A-Za-z.\-]/', '', $info['version']) . '.apk';
    }

    public static function humanSize(int $bytes): string
    {
        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
}
