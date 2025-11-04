<?php

namespace App\Services\USE;

/**
 * Claim Renderer - HTML rendering con colori/badge URS
 * 
 * Renders verified claims with:
 * - Colors based on URS label (A=green, B=blue, C=yellow, X=red)
 * - URS badge
 * - Clickable source links
 * - [Deduzione] badge for inferences
 * 
 * @package App\Services\USE
 * @version 1.0.0
 */
class ClaimRenderer
{
    /**
     * Render claims as HTML
     * 
     * @param array $claims List of verified claims
     * @return string HTML string
     */
    public function renderClaims(array $claims): string
    {
        if (empty($claims)) {
            return '<p class="text-gray-500">' . __('natan.use.no_claims') . '</p>';
        }

        $html = '<div class="use-claims space-y-3">';

        foreach ($claims as $claim) {
            $html .= $this->renderClaim($claim);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render single claim
     * 
     * @param array $claim Claim data
     * @return string HTML string
     */
    public function renderClaim(array $claim): string
    {
        $text = $claim['text'] ?? '';
        $ursLabel = $claim['urs_label'] ?? 'X';
        $urs = $claim['urs'] ?? 0.0;
        $sourceRefs = $claim['source_refs'] ?? [];
        $isInference = $claim['is_inference'] ?? false;

        // Get CSS classes based on URS label
        $colorClasses = $this->getColorClasses($ursLabel);

        // Build claim HTML
        $html = '<div class="use-claim ' . $colorClasses['container'] . ' p-4 rounded-lg border">';

        // URS Badge
        $html .= $this->renderUrsBadge($ursLabel, $urs);

        // Inference badge
        if ($isInference) {
            $html .= '<span class="inline-block ml-2 px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-800">'
                . __('natan.use.inference_badge')
                . '</span>';
        }

        // Claim text with source links
        $html .= '<p class="mt-2 ' . $colorClasses['text'] . '">';
        $html .= $this->renderClaimTextWithLinks($text, $sourceRefs);
        $html .= '</p>';

        // Source links section
        if (!empty($sourceRefs)) {
            $html .= $this->renderSourceLinks($sourceRefs);
        } else {
            $html .= '<p class="mt-2 text-xs text-red-600">'
                . __('natan.use.no_sources')
                . '</p>';
        }

        // URS breakdown (optional, collapsible)
        if (isset($claim['urs_breakdown'])) {
            $html .= $this->renderUrsBreakdown($claim['urs_breakdown']);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get CSS color classes based on URS label
     * 
     * @param string $ursLabel URS label (A, B, C, X)
     * @return array CSS classes
     */
    protected function getColorClasses(string $ursLabel): array
    {
        return match ($ursLabel) {
            'A' => [
                'container' => 'bg-green-50 border-green-200',
                'text' => 'text-green-900',
                'badge' => 'bg-green-500 text-white'
            ],
            'B' => [
                'container' => 'bg-blue-50 border-blue-200',
                'text' => 'text-blue-900',
                'badge' => 'bg-blue-500 text-white'
            ],
            'C' => [
                'container' => 'bg-yellow-50 border-yellow-200',
                'text' => 'text-yellow-900',
                'badge' => 'bg-yellow-500 text-white'
            ],
            'X' => [
                'container' => 'bg-red-50 border-red-200',
                'text' => 'text-red-900',
                'badge' => 'bg-red-500 text-white'
            ],
            default => [
                'container' => 'bg-gray-50 border-gray-200',
                'text' => 'text-gray-900',
                'badge' => 'bg-gray-500 text-white'
            ]
        };
    }

    /**
     * Render URS badge
     * 
     * @param string $label URS label
     * @param float $score URS score
     * @return string HTML
     */
    protected function renderUrsBadge(string $label, float $score): string
    {
        $classes = $this->getColorClasses($label);

        return sprintf(
            '<span class="inline-block px-3 py-1 text-sm font-bold rounded %s">
                URS %s (%.2f)
            </span>',
            $classes['badge'],
            $label,
            $score
        );
    }

    /**
     * Render claim text with clickable source links
     * 
     * @param string $text Claim text
     * @param array $sourceRefs Source references
     * @return string HTML
     */
    protected function renderClaimTextWithLinks(string $text, array $sourceRefs): string
    {
        // For now, return text as-is
        // TODO: Implement smart linking (match text to sources)

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Render source links section
     * 
     * @param array $sourceRefs Source references
     * @return string HTML
     */
    protected function renderSourceLinks(array $sourceRefs): string
    {
        $html = '<div class="mt-3 space-y-1">';
        $html .= '<p class="text-xs font-semibold text-gray-600">' . __('natan.use.sources') . ':</p>';

        foreach ($sourceRefs as $ref) {
            $url = $ref['url'] ?? '#';
            $title = $ref['title'] ?? __('natan.use.unknown_source');
            $page = $ref['page'] ?? null;

            // I18N: Build link text using translation keys (no hardcoded text)
            $linkText = $title;
            if ($page) {
                $linkText .= ' ' . __('natan.use.page_number', ['page' => $page]);
            }

            $html .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" class="block text-xs text-blue-600 hover:underline">
                    → %s
                </a>',
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($linkText, ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render URS breakdown (collapsible)
     * 
     * @param array $breakdown URS breakdown data
     * @return string HTML
     */
    protected function renderUrsBreakdown(array $breakdown): string
    {
        $html = '<details class="mt-2">';
        $html .= '<summary class="text-xs text-gray-600 cursor-pointer hover:text-gray-800">' . __('natan.use.urs_breakdown') . '</summary>';
        $html .= '<div class="mt-2 text-xs space-y-1">';

        foreach ($breakdown as $component => $score) {
            if ($component === 'total') {
                continue;
            }

            $html .= sprintf(
                '<div class="flex justify-between">
                    <span>%s:</span>
                    <span class="font-semibold">%.2f</span>
                </div>',
                ucfirst(str_replace('_', ' ', $component)),
                $score
            );
        }

        $html .= '</div>';
        $html .= '</details>';

        return $html;
    }

    /**
     * Render blocked claims (claims with URS < 0.5)
     * 
     * @param array $blockedClaims Blocked claims
     * @return string HTML
     */
    public function renderBlockedClaims(array $blockedClaims): string
    {
        if (empty($blockedClaims)) {
            return '';
        }

        $html = '<div class="use-blocked-claims mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">';
        $html .= '<p class="font-semibold text-red-800">' . __('natan.use.blocked_claims_title') . '</p>';
        $html .= '<p class="text-sm text-red-600 mt-1">' . __('natan.use.blocked_claims_explanation') . '</p>';

        $html .= '<ul class="mt-2 space-y-1 text-sm text-red-700">';
        foreach ($blockedClaims as $claim) {
            $html .= '<li>• ' . htmlspecialchars($claim['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul>';

        $html .= '</div>';

        return $html;
    }
}
