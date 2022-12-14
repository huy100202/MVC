
<?php
class menu_controller extends main_controller
{
    //public $components = array('SimpleImage');

    public function index()
    {
        $products = product_model::getInstance();
        $conditions = ['conditions' => null, 'limit' => null];
        $page = 1;
        if (isset($_GET['category_id'])) {
            $array = explode(',', $_GET['category_id']);
            $i = 0;
            foreach ($array as $v) {
                if ($i) {
                    $conditions['conditions'] .= ' or ';
                }
                $conditions['conditions'] .= 'categorie_id=' . $v;
                $i++;
            }
        }
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $conditions['limit'] .= ' limit ' . strval(12 * ($page - 1)) . ',12';
        $this->numRows = $products->getNumRows($conditions);
        $this->records = $products->getAllRecordsHasRelated('*', $conditions);
        //$this->setProperty('records',$records);
        $this->display();
    }
    public function addAjax()
    {
        // print_r($_POST);
        $product = product_model::getInstance();
        $session = $product->getRecordHasRelated($_POST['value']);
        $session['amount'] = $_POST['amount'];
        // print_r($a)
        $_SESSION['cart'][$session['id']] = $session;
        print_r($_SESSION);
        exit();
    }
    // public function add()
    // {
    //     if(isset($_POST['btn_submit'])) {
    //         $productData = $_POST['data'][$this->controller];
    //         if(!empty($productData['fullname']))  {
    //             $productData['photo'] = SimpleImage_Component::uploadImg($_FILES, $this->controller);
    //             $product = product_model::getInstance();
    //             if($product->addRecord($productData))
    //                 header( "Location: ".html_helpers::url(array('ctl'=>'products')));
    //         }
    //     }
    //     $this->display();
    // }

    // public function edit($id) {
    //     $product = product_model::getInstance();
    //     $record = $product->getRecord($id);
    //     $this->setProperty('record',$record);
    //     if(isset($_POST['btn_submit'])) {
    //         $productData = $_POST['data'][$this->controller];
    //         if(!empty($productData['fullname']))  {
    //             if(isset($_FILES) and $_FILES["image"]["name"]) {
    //                 if(file_exists(UploadREL .$this->controller.'/'.$record['photo']))
    //                     unlink(UploadREL .$this->controller.'/'.$record['photo']);
    //                 $productData['photo'] = SimpleImage_Component::uploadImg($_FILES, $this->controller);
    //             }
    //             if($product->editRecord($id, $productData))
    //                 header( "Location: ".html_helpers::url(array('ctl'=>'products')));
    //         }
    //     }
    //     $this->display();$user
    // }
    public function deleteAjax()
    {
        unset($_SESSION['cart'][$_POST['value']]);
        echo 'successful';
        exit();
    }
    public function view($id)
    {
        $product = product_model::getInstance();
        $record = $product->getRecordHasRelated($id);
        $this->setProperty('record', $record);
        $this->display();
    }
    public function login()
    {
        unset($_SESSION['cart']);
        $this->check['0'] = null;
        if (isset($_POST['btn_submit'])) {
            if ($this->checkSpecialChar($_POST)) {
                unset($_POST['btn_submit']);
                $user = user_model::getInstance();
                $this->check = $user->checkExist($_POST);
                if ($this->check['0'] == 1) {
                    $_SESSION['login'] = $user->getRecord(null, '*', $_POST);
                    header("Location: " . html_helpers::url(array('ctl' => 'menu')));
                }
            }
        }
        $this->display();
    }
    public function logout()
    {
        unset($_SESSION['login']);
        unset($_SESSION['cart']);
        header("Location: " . html_helpers::url(array('ctl' => 'menu')));
    }
    public function changePass()
    {   
        if(!isset($_SESSION['login'])){
            header("Location: " . html_helpers::url(array('ctl' => 'menu')));
        }
        if (isset($_POST['btn_submit'])) {
            if($_POST['current_password']!=$_SESSION['login']['password']){
                $this->check=['1'=>'current password is incorrect, please re-enter.'];
            }else if($_POST['new_password']!=$_POST['password']){
                $this->check=['2'=>'repeat new password is incorrect, please re-enter.'];
            }else{
                $user = user_model::getInstance();
                $user->editRecord($_SESSION['login']['id'],['password'=>$_POST['new_password']]);
                $_SESSION['login']['password']=$_POST['new_password'];
                header("Location: " . html_helpers::url(array('ctl' => 'menu')));
            }
        }
        $this->display();
    }
    public function register()
    {   
        $this->check['0'] = null;
        if (isset($_POST['btn_submit'])) {
            if ($this->checkSpecialChar($_POST)) {
                $userData = $_POST;
                unset($userData['btn_submit']);
                if (!empty($userData['email'])) {
                    $user = user_model::getInstance();
                    $this->check = $user->checkExist(['email'=>$_POST['email']]);
                    if($_POST['repeat_password']!=$_POST['password']){
                        $this->error=['1'=>'repeat new password is incorrect, please re-enter.'];
                    }
                    if ($this->check['0'] == 0) {
                        $userData['photo'] = SimpleImage_Component::uploadImg($_FILES, 'users')==''?'no-avatar.png':SimpleImage_Component::uploadImg($_FILES, 'users'); 
                        unset($userData['repeat_password']);
                        $user->addRecord($userData);
                        header("Location: " . html_helpers::url(array('ctl' => 'menu', 'act' => 'login')));
                    }
                }
            }
        }
        $this->display();
    }
    public function cart()
    {
        $this->display();
    }
    // public function del($id)
    // {
    //     $product = product_model::getInstance();
    //     $record = $product->getRecord($id);
    //     if(file_exists(RootURI."/media/upload/" .$this->controller.'/'.$record['photo']))
    //         unlink(RootURI."/media/upload/" .$this->controller.'/'.$record['photo']);
    //     echo $product->delRecord($id);
    //     exit;
    //     //$product->delRecord($id);
    //     //header( "Location: ".html_helpers::url(array('ctl'=>'products')));
    // }
}