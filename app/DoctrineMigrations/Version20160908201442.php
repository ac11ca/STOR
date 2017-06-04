<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160908201442 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $env = $this->container->get('kernel')->getEnvironment(); 
        if($env == 'dev')
        {
            $this->addSql(
<<<'EOT'
                INSERT INTO `fos_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `created`) VALUES (NULL,'admintest', 'admintest', 'ZGpoZmg4MmhzZ2RpZmpheBW6nkBpNZJ9wKDNbyhLY7wA0ck2yIky6oMh3KwAyJNc', 'ZGpoZmg4MmhzZ2RpZmpheBW6nkBpNZJ9wKDNbyhLY7wA0ck2yIky6oMh3KwAyJNc', '1', 'ag1xm2p8oo0gok8w8ko48s8cggco4so', '$2y$13$MuC89c0T9imBoVn9c2lsAuKCIWHxsPz94VyYRE4DZ4j2X2cUTnuuW', NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_ADMIN";}', UNIX_TIMESTAMP())
EOT
            );            
        }
        else
        {
            $this->addSql(
<<<'EOT'
                INSERT INTO `fos_user` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `created`) VALUES (NULL,'admin', 'admin', 'ZGpoZmg4MmhzZ2RpZmpheBW6nkBpNZJ9wKDNbyhLY7wA0ck2yIky6oMh3KwAyJNc', 'ZGpoZmg4MmhzZ2RpZmpheBW6nkBpNZJ9wKDNbyhLY7wA0ck2yIky6oMh3KwAyJNc', '1', 'ag1xm2p8oo0gok8w8ko48s8cggco4so', '$2y$13$Wsk3y9Xq/8J2coWAhziGB.R4ra7GgT3SDqA73UANwrShZiHuCdatq', NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_ADMIN";}', UNIX_TIMESTAMP())
EOT
            );
        }

        $this->addSql(
<<<EOT
                INSERT INTO `setting` (`setting_key`,`value`) 
                VALUES
                    ('paginator_items_per_page',6),
                    ('ga_account',''),
                    ('html_header',''),
                    ('html_footer','')
EOT
            );            

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
