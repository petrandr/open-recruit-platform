<?php

namespace App\Orchid\Fields;

use Orchid\Screen\Field;

/**
 * Class Ckeditor.
 *
 * @method $this autofocus($value = true)
 * @method $this disabled($value = true)
 * @method $this form($value = true)
 * @method $this formaction($value = true)
 * @method $this formenctype($value = true)
 * @method $this formmethod($value = true)
 * @method $this formnovalidate($value = true)
 * @method $this formtarget($value = true)
 * @method $this name(string $value = null)
 * @method $this placeholder(string $value = null)
 * @method $this readonly($value = true)
 * @method $this required(bool $value = true)
 * @method $this tabindex($value = true)
 * @method $this value($value = true)
 * @method $this help(string $value = null)
 * @method $this height($value = '300px')
 * @method $this title(string $value = null)
 * @method $this popover(string $value = null)
 * @method $this toolbar(array $options)
 * @method $this base64(bool $value = true)
 * @method $this groups(string $value = null)
 */
class Ckeditor extends Field
{
    /**
     * The Blade view used to render the field.
     *
     * @var string
     */
    protected $view = 'orchid.fields.ckeditor';

    /**
     * Default attributes for the field.
     *
     * @var array
     */
    protected $attributes = [
        'placeholder' => 'Enter text...',
        'class'       => 'form-control js-ckeditor',
        'type'        => 'text',
    ];

    /**
     * List of attributes available for the HTML tag.
     *
     * @var array
     */
    protected $inlineAttributes = [
        'accesskey',
        'autofocus',
        'cols',
        'disabled',
        'form',
        'maxlength',
        'name',
        'placeholder',
        'readonly',
        'required',
        'rows',
        'tabindex',
        'wrap',
    ];
}
