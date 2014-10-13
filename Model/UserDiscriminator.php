<?php

namespace PUGX\MultiUserBundle\Model;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Description of UserDiscriminator
 *
 * @author leonardo proietti (leonardo.proietti@gmail.com)
 * @author eux (eugenio@netmeans.net)
 */
class UserDiscriminator
{
    const SESSION_NAME = 'pugx_user.user_discriminator.class';

    /**
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     *
     * @var array Configuration built from the parameters in config file
     */
    protected $conf = array();

    /**
     *
     * @var array of user types. e.g. array('user_type' => 'user_entity', ..)
     */
    protected $userTypes = array();

    /**
     *
     * @var Symfony\Component\Form\Form
     */
    protected $registrationForm = null;

    /**
     *
     * @var Symfony\Component\Form\Form
     */
    protected $profileForm = null;

    /**
     *
     * @var string
     */
    protected $class = null;

    /**
     * Current form
     * @var type
     */
    protected $form = null;

    /**
     *
     * @param SessionInterface $session
     * @param array $conf The configuration of PUGXMultiUserBundle
     * @param array $userTypes An array containing key-value pairs like this ('user_type' => 'user_entity')
     */
    public function __construct(SessionInterface $session, array $conf, array $userTypes)
    {
        $this->session = $session;
        $this->conf = $conf;
        $this->userTypes = $userTypes;
    }

    /**
     *
     * @return array
     */
    public function getClasses()
    {
        $classes = array();
        foreach ($this->conf as $entity => $conf) {
            $classes[] = $entity;
        }

        return $classes;
    }

    /**
     *
     * @param string $class
     */
    public function setClass($class, $persist = false)
    {
        if (!in_array($class, $this->getClasses())) {
            throw new \LogicException(sprintf('Impossible to set the class discriminator, because the class "%s" is not present in the entities list', $class));
        }

        if ($persist) {
            $this->session->set(static::SESSION_NAME, $class);
        }

        $this->class = $class;
    }

    /**
     *
     * @return string
     */
    public function getClass()
    {
        if (!is_null($this->class)) {
            return $this->class;
        }

        $storedClass = $this->session->get(static::SESSION_NAME, null);

        if ($storedClass) {
            $this->class = $storedClass;
        }

        if (is_null($this->class)) {
            $entities = $this->getClasses();
            $this->class = $entities[0];
        }

        return $this->class;
    }

    /**
     *
     * @return type
     */
    public function createUser()
    {
        $factory = $this->getUserFactory();
        $user    = $factory::build($this->getClass());

        return $user;
    }

    /**
     *
     * @return type
     */
    public function createUserByType($type)
    {
        /** Check that the user type exists */
        if (!array_key_exists($type, $this->userTypes)){
            throw new \InvalidArgumentException('This user type does not exist');
        }
        $class = $this->userTypes[$type];
        $factory = $this->getUserFactory();
        $user    = $factory::build($class);

        return $user;
    }

    /**
     *
     * @return string
     */
    public function getUserFactory()
    {
        return $this->conf[$this->getClass()]['factory'];
    }

    /**
     *
     * @param string $name
     * @return
     * @throws \InvalidArgumentException
     */
    public function getFormType($name)
    {
        $class = $this->getClass();
        $className = $this->conf[$class][$name]['form']['type'];

        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('UserDiscriminator, error getting form type : "%s" not found', $className));
        }

        $type = new $className($class);

        return $type;
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public function getFormName($name)
    {
        return $this->conf[$this->getClass()][$name]['form']['name'];
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function getFormValidationGroups($name)
    {
        return $this->conf[$this->getClass()][$name]['form']['validation_groups'];
    }

    /**
     *
     * @return string
     */
    public function getTemplate($name)
    {
        return $this->conf[$this->getClass()][$name]['template'];
    }

}
