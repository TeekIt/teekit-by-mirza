<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\Pages;

class AdminController extends Controller
{
    /**
     * Return's admin settings view
     *
     * @author Muhammad Abdullah Mirza
     */
    public function settings()
    {
        $pageTypes = ['terms', 'help', 'faq', 'slogan', 'favicon', 'logo'];
        $pages = Pages::whereIn('page_type', $pageTypes)->get()->keyBy('page_type');

        $termsPage = $pages->get('terms');
        $helpPage = $pages->get('help');
        $faqPage = $pages->get('faq');
        $slogan = $pages->get('slogan');
        $favicon = $pages->get('favicon');
        $logo = $pages->get('logo');

        return view('admin.settings', compact(
            'termsPage',
            'helpPage',
            'faqPage',
            'slogan',
            'favicon',
            'logo'
        ));
    }
}
