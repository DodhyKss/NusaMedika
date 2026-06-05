<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Modul;

class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'sidebar_moduls_user_' . $user->user_id; // Using user_id based on previous convention, but let's check what auth user uses for ID. Actually in Laravel $user->id works for the primary key. Let's use $user->id. wait. User model uses string IDs? Auth::id() is safe.

            // Cache data sidebar selama 24 jam untuk user ini sebagai JSON murni
            $jsonModuls = Cache::remember('sidebar_moduls_user_' . Auth::id(), now()->addHours(24), function () use ($user) {
                $aksesSubMenuIds = $user->akses()->pluck('sub_menu_id')->toArray();

                return Modul::where('status_batal', '!=', 1)
                    ->orWhereNull('status_batal')
                    ->with(['menus' => function ($query) use ($aksesSubMenuIds) {
                        $query->where('status_batal', '!=', 1)
                              ->orWhereNull('status_batal')
                              ->with(['subMenus' => function ($query) use ($aksesSubMenuIds) {
                                  $query->whereIn('sub_menu_id', $aksesSubMenuIds)
                                        ->where(function($q) {
                                            $q->where('status_batal', '!=', 1)
                                              ->orWhereNull('status_batal');
                                        })
                                        ->orderBy('urutan_sub_menu');
                              }])
                              ->whereHas('subMenus', function ($query) use ($aksesSubMenuIds) {
                                  $query->whereIn('sub_menu_id', $aksesSubMenuIds)
                                        ->where(function($q) {
                                            $q->where('status_batal', '!=', 1)
                                              ->orWhereNull('status_batal');
                                        });
                              })
                              ->orderBy('urutan_menu');
                    }])
                    ->whereHas('menus.subMenus', function ($query) use ($aksesSubMenuIds) {
                        $query->whereIn('sub_menu_id', $aksesSubMenuIds)
                              ->where(function($q) {
                                  $q->where('status_batal', '!=', 1)
                                    ->orWhereNull('status_batal');
                              });
                    })
                    ->orderBy('urutan_modul')
                    ->get()
                    ->toJson();
            });

            $view->with('moduls', json_decode($jsonModuls));
        } else {
            $view->with('moduls', collect());
        }
    }
}
