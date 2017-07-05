<?php
/*Settings.php
Settings entity for database based settings for Symfony and Doctrine
Copyright (C) 2016,2017 Daniel Fredriksen
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace AppBundle\Entity;

/**
 * Setting
 */
class ConfigurationSetting
{
    private $Configuration;
    private $id;
    private $settingKey;
    private $value;
    private $created;

    public function __construct(Configuration $Configuration)
    {
        $this->setCreated(time());
        $this->setConfiguration($Configuration);
    }

    public function setConfiguration(Configuration $Configuration)
    {
        $this->Configuration = $Configuration;
        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getConfiguration()
    {
        return $this->Configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSettingKey($settingKey)
    {
        $this->settingKey = $settingKey;

        return $this;
    }

    public function getSettingKey()
    {
        return $this->settingKey;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }


    public function __toString()
    {
        return $this->value;

    }
}

