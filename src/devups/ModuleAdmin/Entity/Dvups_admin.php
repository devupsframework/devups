<?php

/**
 * @Entity @Table(name="dvups_admin")
 * */
class Dvups_admin extends Model implements JsonSerializable {

    /**
     * @Id @GeneratedValue @Column(type="integer")
     * @var int
     * */
    private $id;

    /**
     * @Column(name="name", type="string" , length=255 )
     * @var string
     * */
    private $name;

    /**
     * @Column(name="login", type="string" , length=255 )
     * @var string
     * */
    private $login;

    /**
     * @Column(name="password", type="string" , length=255 )
     * @var string
     * */
    private $password;

    /**
     * @var \Dvups_role
     */
    public $dvups_role;

    private function wd_remove_accents($str, $charset = 'utf-8') {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
        return str_replace(' ', '_', $str); // supprime les autres caractères
    }

    /**
     * @param mixed $login
     */
    public function generateLogin() {//on envoi une liste de login
        $list = "1234567890";
        mt_srand((double) microtime() * 10000);
        $generate = "";
        while (strlen($generate) < 4) {
            $generate .= $list[mt_rand(0, strlen($list) - 1)];
        }

        if (strlen($this->name) > 6)
            $alias = substr($this->name, 0, -(strlen($this->name) - 6));
        else
            $alias = $this->name;

        $this->login = $this->wd_remove_accents($alias) . $generate;
        $login = strtolower($this->login);
        return $login;
    }

    /**
     * @param mixed
     */
    public function generatePassword() {
        $list = "0123456789abcdefghijklmnopqrstvwxyz";
        mt_srand((double) microtime() * 1000000);
        $password = "";
        while (strlen($password) < 8) {
            $password .= $list[mt_rand(0, strlen($list) - 1)];
        }
        return $password;
    }

    public function __construct($id = null) {

        if ($id) {
            $this->id = $id;
        }

        $this->dvups_role = EntityCollection::entity_collection('dvups_role');
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    function getName() {
        return $this->name;
    }

    function setName($name) {
        if(!$name)
            return "name empty";

        $this->name = $name;
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     *  manyToMany
     * 	@return \Dvups_role
     */
    function getDvups_role() {
        return $this->dvups_role;
    }

    function collectDvups_role() {
        $this->dvups_role = $this->__hasmany('dvups_role');
        return $this->dvups_role;
    }

    function addDvups_role(\Dvups_role $dvups_role) {
        $this->dvups_role[] = $dvups_role;
    }

    function dropDvups_roleCollection() {
        $this->dvups_role = EntityCollection::entity_collection('dvups_role');
    }

    function availableentityright($action) {
        if (isset($this->manageentity[$action])) {
            $entity = $this->manageentity[$action];
            return $entity->availableright();
        }
        return [];
    }

    function callbackbtnAction(){
        return "<a class='btn btn-default' href='index.php?path=dvups_admin/resetcredential&id=".$this->getId()."'>reset password</a>";
    }

    public function jsonSerialize() {
        return [
            'login' => $this->login,
            'password' => $this->password,
            'dvups_role' => $this->dvups_role,
        ];
    }

}
