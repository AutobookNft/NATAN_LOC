<?php

namespace App\Http\Controllers;

use App\Services\Menu\ContextMenus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-20
 * @purpose API endpoints for frontend AJAX interactions
 */
class ApiController extends Controller
{
    /**
     * Switch application context
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function switchContext(Request $request): JsonResponse
    {
        $request->validate([
            'context' => 'required|string|in:natan.chat,infraufficio.chat,infraufficio.bacheca'
        ]);

        $newContext = $request->input('context');

        // Store context in session
        Session::put('current_context', $newContext);

        // Get updated sidebar content
        $menus = ContextMenus::getMenusForContext($newContext);

        // Generate sidebar HTML
        $sidebarContent = '';
        foreach ($menus as $menuGroup) {
            if ($menuGroup->hasVisibleItems()) {
                $sidebarContent .= '<div class="mt-4 pt-2 border-t border-slate-200 first:mt-0 first:pt-0 first:border-t-0">';
                $sidebarContent .= '<h3 class="px-2 mb-2 text-[10px] font-bold uppercase tracking-wider font-mono text-slate-400 flex items-center gap-2">';

                if ($menuGroup->icon) {
                    $sidebarContent .= '<span class="w-3 h-3">' . $menuGroup->icon . '</span>';
                }

                $sidebarContent .= htmlspecialchars($menuGroup->name) . '</h3>';
                $sidebarContent .= '<nav class="space-y-0.5">';

                foreach ($menuGroup->items as $item) {
                    $href = $item->getHref();
                    $attributes = $item->getHtmlAttributes();
                    $attrsString = '';

                    foreach ($attributes as $attr => $value) {
                        $attrsString .= ' ' . $attr . '="' . htmlspecialchars($value) . '"';
                    }

                    $sidebarContent .= '<a href="' . htmlspecialchars($href) . '"' . $attrsString;
                    $sidebarContent .= ' class="flex items-center gap-3 px-3 py-2 text-xs font-medium text-slate-600 hover:text-black hover:bg-slate-100 rounded-sm transition-colors group">';

                    if ($item->icon) {
                        $sidebarContent .= '<span class="w-4 h-4 text-slate-400 group-hover:text-slate-600">' . $item->icon . '</span>';
                    }

                    $sidebarContent .= '<span>' . htmlspecialchars($item->name) . '</span>';
                    $sidebarContent .= '</a>';
                }

                $sidebarContent .= '</nav></div>';
            }
        }

        // Get sidebar title translation
        $sidebarTitleKey = 'natan.sidebar.' . str_replace('.', '_', $newContext);
        $sidebarTitle = __($sidebarTitleKey);

        return response()->json([
            'success' => true,
            'context' => $newContext,
            'sidebar_title' => $sidebarTitle,
            'sidebar_content' => $sidebarContent
        ]);
    }
}
