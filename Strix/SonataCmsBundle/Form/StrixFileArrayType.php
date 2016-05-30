<?php

namespace Strix\SonataCmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StrixFileArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAttribute('widget', 'strix_file_array');
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getViewData();
        $view->vars = array_replace($view->vars, array(
            'files'        => $data
        ));
    }

    public function getName()
    {
        return 'strix_file_array';
    }

    public function getParent()
    {
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
}
