<?php

namespace Feederate\FeederateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Feederate\FeederateBundle\Importer\Importer;

/**
 * Importer FormType
 */
class ImporterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('platform', 'choice', array(
                'choices' => array_combine(Importer::getPlatforms(), Importer::getPlatforms())
            ))
            ->add('attachment', 'file');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'importer';
    }
}
