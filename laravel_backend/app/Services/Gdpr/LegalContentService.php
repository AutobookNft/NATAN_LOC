<?php

namespace App\Services\Gdpr;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Ultra\UltraLogManager\UltraLogManager;
use App\Services\Gdpr\ConsentService;

/**
 * @package App\Services\Gdpr
 * @author Padmin D. Curtis (AI Partner OS1.5.1-Compliant) for Fabio Cherici
 * @version 1.1.0 (FlorenceEGI MVP - Personal Data Domain)
 * @date 2025-06-24
 *
 * @Oracode Service: Legal Content Provider & Manager
 * 🎯 Purpose: Centralized service to read AND write versioned legal documents.
 * 🧱 Core Logic: Handles all filesystem interactions: reading, writing, backups, versioning.
 * 📡 API: Provides a stable interface for controllers and other services.
 */
class LegalContentService
{
    protected string $basePath;

    /**
     * Consent service for integration with existing GDPR system
     * @var ConsentService
     */
    protected ConsentService $consentService;



    public function __construct(
            ConsentService $consentService,
            protected ?UltraLogManager $logger = null
            )
        {
            $this->basePath = resource_path('legal/terms/versions');
            $this->consentService = $consentService;

        }

    // ═══════════════════════════════════════════════════════════════════════════════
    // READ METHODS
    // ═══════════════════════════════════════════════════════════════════════════════

    public function getCurrentTermsContent(string $userType, string $locale): ?array
    {
        $cacheKey = "legal_terms_current_{$userType}_{$locale}";
        return Cache::remember($cacheKey, 3600, function () use ($userType, $locale) {
            $filePath = "{$this->basePath}/current/{$locale}/{$userType}.php";
            return $this->loadContentFromFile($filePath);
        });
    }

    public function getCurrentVersionString(): string
    {
        $cacheKey = 'legal_terms_current_version_string';
        return Cache::remember($cacheKey, 3600, function () {
            $metadata = $this->getMetadataForVersion('current');
            return $metadata['version'] ?? '0.0.0';
        });
    }

    public function getMetadataForVersion(string $version): ?array
    {
        $filePath = "{$this->basePath}/{$version}/metadata.php";
        return $this->loadContentFromFile($filePath);
    }

    /**
     * Get user consent status for terms
     *
     * @param string $userType
     * @param string $locale
     * @return array
     */
    public function getUserConsentStatus(string $userType, string $locale): array
    {
        if (!auth()->check()) {
            return [
                'hasAcceptedCurrent' => false,
                'userAcceptedVersion' => null,
                'needsAcceptance' => true
            ];
        }

        $user = auth()->user();
        $hasAcceptedCurrent = $this->consentService->hasConsent($user, 'terms-of-service');

        // Get user's current consent to extract version info
        $userConsent = $user->consents()
            ->where('consent_type', 'terms-of-service')
            ->where('granted', true)
            ->latest('created_at')
            ->first();

        $userAcceptedVersion = null;
        if ($userConsent && isset($userConsent->metadata['version'])) {
            $userAcceptedVersion = $userConsent->metadata['version'];
        }

        return [
            'hasAcceptedCurrent' => $hasAcceptedCurrent,
            'userAcceptedVersion' => $userAcceptedVersion,
            'needsAcceptance' => !$hasAcceptedCurrent || $user->usertype !== $userType
        ];
    }


    public function getVersionHistory(): array
    {
        $versions = [];
        if (!File::exists($this->basePath)) {
            return $versions;
        }

        $directories = File::directories($this->basePath);
        foreach ($directories as $dir) {
            $version = basename($dir);
            if ($version === 'current') continue;

            $metadataPath = "{$dir}/metadata.php";
            if (File::exists($metadataPath)) {
                $versions[] = [
                    'version' => $version,
                    'metadata' => include $metadataPath,
                ];
            }
        }
        usort($versions, fn($a, $b) => version_compare($b['version'], $a['version']));
        return $versions;
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // WRITE, VALIDATION & VERSIONING METHODS
    // ═══════════════════════════════════════════════════════════════════════════════

    public function createNewVersion(string $userType, string $locale, string $content, string $changeSummary, ?string $effectiveDate, bool $autoPublish = false): string
    {
        if (!$this->isContentSecure($content)) {
            throw new \InvalidArgumentException('Il contenuto contiene codice non sicuro o non permesso.');
        }

        $this->backupCurrentVersion($userType, $locale);
        $newVersion = $this->generateNewVersionNumber();
        $versionPath = "{$this->basePath}/{$newVersion}";
        $localePath = "{$versionPath}/{$locale}";

        File::makeDirectory($localePath, 0755, true, true);

        File::put("{$localePath}/{$userType}.php", $content);
        $this->updateVersionMetadata($newVersion, $userType, $locale, $changeSummary, $effectiveDate);

        if ($autoPublish) {
            $this->updateCurrentVersionSymlink($newVersion);
        }

        $this->clearCache();
        return $newVersion;
    }

    public function isContentSecure(string $content): bool
    {
        if (!$this->isValidPHPSyntax($content)) {
            return false;
        }

        $dangerousFunctions = ['exec', 'system', 'shell_exec', 'eval', 'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'include', 'require'];
        foreach ($dangerousFunctions as $func) {
            if (stripos($content, $func) !== false) {
                $this->logger?->warning('LegalContentService: Dangerous function detected', ['function' => $func]);
                return false;
            }
        }
        return true;
    }

    public function clearCache(?string $userType = null, ?string $locale = null): void
    {
        if ($userType && $locale) {
            Cache::forget("legal_terms_current_{$userType}_{$locale}");
        }
        Cache::forget('legal_terms_current_version_string');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════════

    private function loadContentFromFile(string $filePath): ?array
    {
        if (!File::exists($filePath)) {
            $this->logger?->warning('LegalContentService: File not found', ['file_path' => $filePath]);
            return null;
        }
        try {
            return include $filePath;
        } catch (\Throwable $e) {
            $this->logger?->error('LegalContentService: Failed to load content file', ['file_path' => $filePath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function isValidPHPSyntax(string $content): bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'legal_syntax_check');
        file_put_contents($tempFile, "<?php\n" . $content);
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);
        unlink($tempFile);
        return $returnCode === 0;
    }

    private function backupCurrentVersion(string $userType, string $locale): void
    {
        $currentFile = "{$this->basePath}/current/{$locale}/{$userType}.php";
        if (File::exists($currentFile)) {
            $backupDir = resource_path('legal/terms/backups/' . date('Y-m-d'));
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }
            File::copy($currentFile, "{$backupDir}/{$userType}_{$locale}_" . time() . ".php");
        }
    }

    private function generateNewVersionNumber(): string
    {
        $currentVersion = $this->getCurrentVersionString();
        $parts = explode('.', $currentVersion);
        $parts[2] = (int)($parts[2] ?? 0) + 1;
        return implode('.', $parts);
    }

    private function updateVersionMetadata(string $version, string $userType, string $locale, string $changeSummary, ?string $effectiveDate): void
    {
        $metadataPath = "{$this->basePath}/{$version}/metadata.php";
        $metadata = [
            'version' => $version,
            'release_date' => now()->toDateString(),
            'effective_date' => $effectiveDate ?? now()->toDateString(),
            'created_by' => auth()->user()?->email,
            'summary_of_changes' => $changeSummary,
        ];
        File::put($metadataPath, "<?php\n\nreturn " . var_export($metadata, true) . ";\n");
    }

    private function updateCurrentVersionSymlink(string $newVersion): void
    {
        $currentPath = "{$this->basePath}/current";
        $targetPath = realpath("{$this->basePath}/{$newVersion}");
        if (file_exists($currentPath)) {
            unlink($currentPath);
        }
        symlink($targetPath, $currentPath);
    }
}
