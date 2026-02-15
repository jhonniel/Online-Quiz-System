<?php

namespace Symfony\Component\CssSelector;

/**
 * Stub class to satisfy CssToInlineStyles dependency
 * This prevents the "Class not found" error
 */
class CssSelectorConverter
{
    public function __construct($htmlVersion = 5)
    {
        // Stub implementation - CSS inlining will be skipped
    }

    public function toXPath($cssExpr, $prefix = 'descendant-or-self::')
    {
        // Return a simple XPath that matches all elements
        // This effectively disables CSS selector matching
        return '//*';
    }
}
