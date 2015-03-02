
namespace Company\BlogBundle\DataFixtures\ORM;
 
use Doctrine\Common\DataFixtures\FixtureInterface;
use Company\BlogBundle\Entity\Category;
use Company\BlogBundle\Entity\Post;
use Company\BlogBundle\Entity\Tag;
use Company\BlogBundle\Entity\User;
use Company\BlogBundle\Entity\Role;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
 
class FixtureLoader implements FixtureInterface
{
    public function load($manager)
    {
        // create the ROLE_ADMIN role
        $role = new Role();
        $role->setName('ROLE_ADMIN');
 
        $manager->persist($role);
 
        // create a user
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john@example.com');
        $user->setUsername('john.doe');
        $user->setSalt(md5(time()));
 
        // encode and set the password for the user,
        // these settings match our config
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword('admin', $user->getSalt());
        $user->setPassword($password);
 
        $user->getUserRoles()->add($role);
 
        $manager->persist($user);
 
        // ...
 
}