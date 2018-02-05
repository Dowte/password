<?php

namespace Dowte\Password\pass;


class PassSecret
{
    /**
     * @var string $_privateKeyName
     */
    protected $_privateKeyName = '.rsa_private_key.pem';

    /**
     * @var string $_publicKeyName
     */
    protected $_publicKeyName = '.rsa_public_key.pem';

    /**
     * @var string $_privateMatch
     */
    protected $_privateMatch = '%private_key_path%';

    /**
     * @var string $_publicMatch
     */
    protected $_publicMatch = '%public_key_path%';

    /**
     * @var string $_secretKeyDir
     */
    protected $_secretKeyDir = __DIR__ . '/../../';

    /**
     * @var int $_secretKeyBits
     */
    protected $_secretKeyBits = 2048;

    /**
     * @var int $_secretKeyType
     */
    protected $_secretKeyType = OPENSSL_KEYTYPE_RSA;

    /**
     * @var string $publicKeyPath
     */
    public static $publicKeyPath;

    /**
     * @var string $privateKeyPath
     */
    public static $privateKeyPath;

    /**
     * @param $conf
     */
    public static function load($conf)
    {
        foreach ((array) $conf as $property => $item) {
            $property = Password::underline2hump($property);
            ! property_exists(__CLASS__, $property) or static::$$property = $item;
        }
    }

    /**
     * @param $oldData
     * @param $newData
     * @return bool
     */
    public static function validData($oldData, $newData)
    {
        openssl_private_decrypt(base64_decode($oldData), $decrypted, self::getPrivateKey());
        openssl_private_decrypt(base64_decode($newData), $validDecrypted, self::getPrivateKey());
        if ($decrypted === $validDecrypted) {
            return true;
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public static function getPublicKey()
    {
        if (! file_exists(self::$publicKeyPath)) {
            die('Public key is not exits, please init secret key at first!');
        }
        return file_get_contents(self::$publicKeyPath);
    }

    /**
     * @return bool|string
     */
    public static function getPrivateKey()
    {
        if (! file_exists(self::$privateKeyPath)) {
            die('Private key is not exits, please init secret key at first!');
        }
        return file_get_contents(self::$privateKeyPath);
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function encryptData($data)
    {
        openssl_public_encrypt($data, $encrypted, self::getPublicKey());
        return base64_encode($encrypted);
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function decryptedData($data)
    {
        openssl_private_decrypt(base64_decode($data), $decrypted, self::getPrivateKey());
        return $decrypted;
    }

    /**
     * @param $_secretKeyDir
     */
    public function setSecretKeyDir($_secretKeyDir)
    {
        $this->_secretKeyDir = $_secretKeyDir;
    }

    /**
     * @param $length
     */
    public function setSecretKeyBits($length)
    {
        $this->_secretKeyBits = $length;
    }

    /**
     * @param $type
     */
    public function setSecretKeyType($type)
    {
        $this->_secretKeyType = $type;
    }

    /**
     * @return bool
     */
    public function buildSecretKey()
    {
        if (str_replace($this->_privateMatch, '', self::$privateKeyPath) && str_replace($this->_privateMatch, '', self::$publicKeyPath)) {
            die('The secret keys ware already exist !');
        }
        $config = [
            "private_key_bits" => $this->_secretKeyBits,
            "private_key_type" => $this->_secretKeyType,
        ];
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];
        $piKey = openssl_pkey_get_private($privateKey);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $puKey = openssl_pkey_get_public($publicKey);//这个函数可用来判断公钥是否是可用的
        if ($piKey && $puKey) {
            $this->saveSecretKeys($privateKey, $publicKey);
            return true;
        } else {
            die('Build secret key false !');
        }
    }

    public function secretKeyTemplateTo($privateKeyPath, $publicKeyPath)
    {
        $content = str_replace(
            [$this->_privateMatch, $this->_publicMatch],
            [$privateKeyPath, $publicKeyPath],
            file_get_contents(CONF_FILE)
        );
        file_put_contents(CONF_FILE, $content);
    }

    public function toSecretKeyTemplate($privateKeyPath, $publicKeyPath)
    {
        $content = str_replace(
            [$privateKeyPath, $publicKeyPath],
            [$this->_privateMatch, $this->_publicMatch],
            file_get_contents(CONF_FILE)
        );
        file_put_contents(CONF_FILE, $content);
    }


    /**
     * @param $privateKey
     * @param $publicKey
     */
    private function saveSecretKeys($privateKey, $publicKey)
    {
        $privateKeyPath = rtrim($this->_secretKeyDir, '/') . '/' . $this->_privateKeyName;
        $publicKeyPath = rtrim($this->_secretKeyDir, '/') . '/' . $this->_publicKeyName;
        $this->secretKeyTemplateTo($privateKeyPath, $publicKeyPath);
        $secret = [$privateKeyPath => $privateKey, $publicKeyPath => $publicKey];
        foreach ($secret as $k => $item) {
            $fp = fopen($k, 'w+');
            fwrite($fp, $item);
            fclose($fp);
        }
        Password::init(require CONF_FILE);
    }
}