<?php
namespace Fyber\Widgets\Form;

interface FormElementInterface
{
    public function setName($name);
    public function getName();
    public function setValue($value);
    public function getValue();
    public function show();
}
