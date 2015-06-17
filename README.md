Presta Form Builder
=====================

Presta Form Builder is a utility class, based on Laravel Html and Form Builders code, to allow PrestaShop developers to create easily great Forms without worrying about html and without using the awful PrestaShop FormHelper which is not very flexible...

It's basically Laravel's code put in some static functions instead of a Facade.

# Installation

Just put `Form.php` into your Prestashop's classes folder (or your module's classes folder but you'll have to include it manually) and enjoy using it.

# Status

I didn't put all Laravel's Form and Html helpers into it cause I don't need them right now, but I'll do it soon.

# Usage

## In a Smarty template

### Simple example

You can call FormBuilder function inside smarty, this way :
```
{Form::open()}
    {Form::text('name');}
{Form::close()}
```

### With array

Smarty does not allow usage of array inside function calls, so you'll have to declare your array before :

```
{$opt = ['class' => 'hidden']}
{Form::open()}
    {Form::text('name', 'some text', $opt)}
{Form::close()}
```

### Translation

Unfortunately there isn't a nice way to do translation inside Smarty when you call a php function, so the best way is to do the translation in the module then pass values to smarty.

```
$this->context->smarty->assign([
    's_translated_text' => $this->l('String to translate'),
    ]);
```

Or if you really want to do it with smarty, use this ugly syntax :

```
{capture name='my_module_tempvar'}{l s='String to translate' mod='mymodule'}{/capture}
{assign var='my_module_name' value=$smarty.capture.my_module_tempvar}
{Form::text('name', $my_module_name, $opt)}
```

Both solutions aren't realy elegant but, AFAIK smarty and PS doesn't provide any other way to put translation inside a variable.

## In PHP

Naturally, you can call the Form helper inside PHP as well :

```
echo Form::open();
```

# Admin helpers

I added some function to help you build admin panel forms.

It surround an input with a label inside a bootstrap 'form-group' div.

You can use them by adding a 'a' before the form element name eg :

```
{Form::atext('element_name', 'Displayed label', 'value', ['required'])}
```

## imgsubmit

Submit button with an image.

Warning : This example does not use translation

```
{Form::imgsubmit('submit_config', 'Save', 'process-icon-save')}
```