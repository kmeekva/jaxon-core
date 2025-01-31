<?php

namespace Jaxon\App\View;

/**
 * AttrHelper.php
 *
 * Formatter for Jaxon custom HTML attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Component;
use Jaxon\Di\ClassContainer;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;

use function count;
use function htmlentities;
use function is_a;
use function is_array;
use function is_string;
use function json_encode;
use function trim;

class AttrHelper
{
    /**
     * The constructor
     *
     * @param ClassContainer $cls
     */
    public function __construct(protected ClassContainer $cls)
    {}

    /**
     * Get the component HTML code
     *
     * @param JxnCall $xJsCall
     *
     * @return string
     */
    public function html(JxnCall $xJsCall): string
    {
        $sClassName = $xJsCall->_class();
        if(!$sClassName)
        {
            return '';
        }

        $xCallable = $this->cls->makeRegisteredObject($sClassName);
        return is_a($xCallable, Component::class) ? $xCallable->html() : '';
    }

    /**
     * Attach a component to a DOM node
     *
     * @param JxnCall $xJsCall
     * @param string $item
     *
     * @return string
     */
    public function show(JxnCall $xJsCall, string $item = ''): string
    {
        $item = trim($item);
        return 'jxn-show="' . $xJsCall->_class() . (!$item ? '"' : '" jxn-item="' . $item . '"');
    }

    /**
     * Set a node as a target for event handler definitions
     *
     * @param string $name
     *
     * @return string
     */
    public function target(string $name = ''): string
    {
        return 'jxn-target="' . trim($name) . '"';
    }

    /**
     * @param array $on
     *
     * @return bool
     */
    private function checkOn(array $on)
    {
        // Only accept arrays of 2 entries.
        $count = count($on);
        if($count !== 2)
        {
            return false;
        }

        // Only accept arrays with int index from 0, and string value.
        for($i = 0; $i < $count; $i++)
        {
            if(!isset($on[$i]) || !is_string($on[$i]))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the event handler attributes
     *
     * @param string $select
     * @param string $event
     * @param string $attr
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    private function eventAttr(string $select, string $event, string $attr, JsExpr $xJsExpr): string
    {
        $sCall = htmlentities(json_encode($xJsExpr->jsonSerialize()));

        return "$attr=\"$event\" jxn-call=\"$sCall\"" .
            ($select !== '' ? "jxn-select=\"$select\" " : '');
    }

    /**
     * Set an event handler with the "on" keywork
     *
     * @param string|array $on
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function on(string|array $on, JsExpr $xJsExpr): string
    {
        $select = '';
        $event = $on;
        if(is_array($on))
        {
            if(!$this->checkOn($on))
            {
                return '';
            }

            $select = trim($on[0]);
            $event = $on[1];
        }
        $event = trim($event);

        return $this->eventAttr($select, $event, 'jxn-on', $xJsExpr);
    }

    /**
     * Shortcut to set a click event handler
     *
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function click(JsExpr $xJsExpr): string
    {
        return $this->on('click', $xJsExpr);
    }

    /**
     * Set an event handler with the "event" keywork
     *
     * @param array $on
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function event(array $on, JsExpr $xJsExpr): string
    {
        if(!$this->checkOn($on))
        {
            return '';
        }

        return $this->eventAttr(trim($on[0]), trim($on[1]), 'jxn-event', $xJsExpr);
    }
}
