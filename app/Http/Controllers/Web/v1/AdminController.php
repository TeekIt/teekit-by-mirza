<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Pages;
use App\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Return's admin settings view
     * @author Muhammad Abdullah Mirza
     */
    public function settings()
    {
        $pageTypes = ['terms', 'help', 'faq', 'slogan', 'favicon', 'logo'];
        $pages = Pages::whereIn('page_type', $pageTypes)->get()->keyBy('page_type');

        $terms_page = $pages->get('terms');
        $help_page = $pages->get('help');
        $faq_page = $pages->get('faq');
        $slogan = $pages->get('slogan');
        $favicon = $pages->get('favicon');
        $logo = $pages->get('logo');

        return view('admin.settings', compact(
            'terms_page',
            'help_page',
            'faq_page',
            'slogan',
            'favicon',
            'logo'
        ));
    }
    /**
     * Delete selected users
     * @author Muhammad Abdullah Mirza
     */
    public function deleteUsers(Request $request)
    {
        User::adminUsersDel($request);

        return response(config('constants.USERS_DELETION_SUCCESS'));
    }
    /**
     * Delete selected drivers
     * @author Muhammad Abdullah Mirza
     */
    public function deleteDrivers(Request $request)
    {
        Driver::adminDriversDel($request);

        return response(config('constants.DRIVERS_DELETION_SUCCESS'));
    }
}
