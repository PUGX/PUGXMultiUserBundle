<?php

namespace PUGX\MultiUserBundle\Tests\Doctrine;

use PUGX\MultiUserBundle\Doctrine\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->encoderFactory = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\EncoderFactory')
                ->disableOriginalConstructor()->getMock();
        $this->usernameCanonicalizer = $this->getMockBuilder('FOS\UserBundle\Util\Canonicalizer')
                ->disableOriginalConstructor()->getMock();
        $this->emailCanonicalizer = $this->getMockBuilder('FOS\UserBundle\Util\Canonicalizer')
                ->disableOriginalConstructor()->getMock();
        $this->om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()->getMock();
        $this->userDiscriminator = $this->getMockBuilder('PUGX\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();
        $this->class = 'PUGX\MultiUserBundle\Tests\Stub\User';
        
        $this->repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
                ->disableOriginalConstructor()->getMock();
        
        //parent
        $this->metaData = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
                ->disableOriginalConstructor()->getMock();
        
        $this->om->expects($this->exactly(1))->method('getClassMetadata')
                ->with($this->class)
                ->will($this->returnValue($this->metaData));
        
        $this->metaData->expects($this->exactly(1))->method('getName')
                ->will($this->returnValue($this->class));
        //end parent
        
        $this->userManager = new UserManager($this->encoderFactory, $this->usernameCanonicalizer, $this->emailCanonicalizer, $this->om, $this->class, $this->userDiscriminator);
    }
        
    public function testGetClass()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('getClass')->will($this->returnValue('Acme\UserBundle\MyUser'));        
        $result = $this->userManager->getClass();
        $this->assertEquals('Acme\UserBundle\MyUser', $result);
    }
    
    public function testCreateUser()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('createUser')->will($this->onConsecutiveCalls(null));        
        $this->userManager->createUser();
    }
    
    public function testFindUserBy()
    {                
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(array('PUGX\MultiUserBundle\Tests\Stub\User')));
        $this->om->expects($this->exactly(1))->method('getRepository')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findOneBy')->with(array('criteria' => 'dummy'))->will($this->onConsecutiveCalls(true));
        $this->userManager->findUserBy(array('criteria' => 'dummy'));
    }
    
    public function testFindUsers()
    {                
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(array('PUGX\MultiUserBundle\Tests\Stub\User')));
        $this->om->expects($this->exactly(1))->method('getRepository')->with('PUGX\MultiUserBundle\Tests\Stub\User')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findAll')->will($this->onConsecutiveCalls(array()));                
        $this->userManager->findUsers();
    }
    
    public function testFindUserByUserNotFound()
    {                
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(array('PUGX\MultiUserBundle\Tests\Stub\User')));
        $this->om->expects($this->exactly(1))->method('getRepository')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findOneBy')->with(array('criteria' => 'dummy'))->will($this->onConsecutiveCalls(null));        
        $this->userDiscriminator->expects($this->exactly(0))->method('setClass');                
        $user = $this->userManager->findUserBy(array('criteria' => 'dummy'));        
        $this->assertEquals(null, $user);
    }

    public function testRefreshUserDiscriminatorClasses()
    {
        $user = $this->getMock('FOS\UserBundle\Model\User');
        $class = get_class($user);
        $this->userDiscriminator->expects($this->any())->method('getClass')->will($this->onConsecutiveCalls($this->class, $class));
        $this->userDiscriminator->expects($this->any())->method('getClasses')->will($this->returnValue(array($this->class, $class)));
        $this->userDiscriminator->expects($this->exactly(1))->method('setClass')->with($class);

        $this->om->expects($this->any())->method('getRepository')->will($this->returnValue($this->repo));
        $this->repo->expects($this->any())->method('findOneBy')->will($this->returnValue($user));

        $this->assertSame($user, $this->userManager->refreshUser($user));
    }
}
