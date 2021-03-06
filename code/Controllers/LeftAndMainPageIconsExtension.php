<?php

namespace SilverStripe\CMS\Controllers;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Convert;
use SilverStripe\View\Requirements;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;

/**
 * Extension to include custom page icons
 */
class LeftAndMainPageIconsExtension extends Extension
{

    public function init()
    {
        Requirements::customCSS($this->generatePageIconsCss(), CMSMain::PAGE_ICONS_ID);
    }

    /**
     * Include CSS for page icons. We're not using the JSTree 'types' option
     * because it causes too much performance overhead just to add some icons.
     *
     * @return string CSS
     */
    public function generatePageIconsCss()
    {
        $css = '';

        $classes = ClassInfo::subclassesFor(SiteTree::class);
        foreach ($classes as $class) {
            $obj = singleton($class);
            $iconSpec = $obj->config()->get('icon');

            if (!$iconSpec) {
                continue;
            }

            // Legacy support: We no longer need separate icon definitions for folders etc.
            $iconFile = (is_array($iconSpec)) ? $iconSpec[0] : $iconSpec;

            // Legacy support: Add file extension if none exists
            if (!pathinfo($iconFile, PATHINFO_EXTENSION)) {
                $iconFile .= '-file.gif';
            }

            $class = Convert::raw2htmlid($class);
            $selector = ".page-icon.class-$class, li.class-$class > a .jstree-pageicon";
            if (Director::fileExists($iconFile)) {
                $css .= "$selector { background: transparent url('$iconFile') 0 0 no-repeat; }\n";
            } else {
                // Support for more sophisticated rules, e.g. sprited icons
                $css .= "$selector { $iconFile }\n";
            }
        }

        return $css;
    }
}
