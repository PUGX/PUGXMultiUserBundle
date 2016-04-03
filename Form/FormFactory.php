<?php

namespace Massil\MultiUserBundle\Form;

use Massil\MultiUserBundle\Model\UserDiscriminator;
use Symfony\Component\Form\FormFactoryInterface;
use FOS\UserBundle\Form\Factory\FactoryInterface;

class FormFactory implements FactoryInterface
{
    /**
     *
     * @var \Massil\MultiUserBundle\Model\UserDiscriminator 
     */
    private $userDiscriminator;
    
    /**
     *
     * @var FormFactoryInterface 
     */
    private $formFactory;
    
    /**
     *
     * @var string 
     */
    private $type;
    
    /**
     *
     * @var array 
     */
    private $forms = array();
    
    /**
     * 
     * @param \Massil\MultiUserBundle\Model\UserDiscriminator $userDiscriminator
     * @param string $type registration|profile
     */
    public function __construct(UserDiscriminator $userDiscriminator, FormFactoryInterface $formFactory, $type) 
    {
        $this->userDiscriminator = $userDiscriminator;
        $this->formFactory = $formFactory;
        $this->type = $type;
    }
    
    /**
     * 
     * @return \Symfony\Component\Form\Form 
     */
    public function createForm()
    {
        $type = $this->userDiscriminator->getFormType($this->type);
        $name = $this->userDiscriminator->getFormName($this->type);
        $validationGroups = $this->userDiscriminator->getFormValidationGroups($this->type);
        
        if (array_key_exists($name, $this->forms)) {
            return $this->forms[$name];
        }
            
        $form = $this->formFactory->createNamed(
                $name, 
                get_class($type), 
                null, 
                array('validation_groups' => $validationGroups));
        
        $this->forms[$name] = $form;
        
        return $form;
    }
}