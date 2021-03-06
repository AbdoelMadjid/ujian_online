<?php
    $root = "../";
    include $root."includes/php/initial.php";
    include $root."includes/php/classUtama.php";
    $db = new classUtama(ADMIN_SESSION_NAME);
    $db->mysql_connect();

    //validasi-validasi
    if(strlen($_POST["nama_sekolah"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Sekolah harus diisi.")));
    } else {
        $sekolah = $db->getFieldValue("sekolah", array("COUNT(*) AS TOTAL"), array("id" => $_POST["id_sekolah"]));
        if($sekolah[0]["TOTAL"]==0) {
            $db->mysql_close();
            die(json_encode(array("success" => false, "message" => "Nama sekolah tidak terdaftar.")));
        }
    }
    
    if(strlen($_POST["nama_jurusan"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Jurusan harus diisi.")));
    } else {
        $jurusan = $db->getFieldValue("jurusan", array("COUNT(*) AS TOTAL"), array("id" => $_POST["id_jurusan"]));
        if($jurusan[0]["TOTAL"]==0) {
            $db->mysql_close();
            die(json_encode(array("success" => false, "message" => "Jurusan tidak terdaftar.")));
        }
    }
    
    if(strlen($_POST["nama_subjurusan"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Sub Jurusan harus diisi.")));
    } else {
        $subjurusan = $db->getFieldValue("subjurusan", array("COUNT(*) AS TOTAL"), array("id" => $_POST["id_subjurusan"]));
        if($subjurusan[0]["TOTAL"]==0) {
            $db->mysql_close();
            die(json_encode(array("success" => false, "message" => "Sub Jurusan tidak terdaftar.")));
        }
    }
    if(strlen($_POST["nik"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "NIK harus diisi.")));
    } else {
        $user_count = $db->getFieldValue("guru", array("COUNT(*) AS TOTAL"), array("nik" => $_POST["nik"], "id_subjurusan" => $_POST["id_subjurusan"]));
        if($user_count[0]["TOTAL"]>0) {
            $db->mysql_close();
            die(json_encode(array("success" => false, "message" => "NIK sudah terpakai.")));
        }
    }
    
    if(strlen($_POST["nama"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Nama guru harus diisi.")));
    }
    
    if(strlen($_POST["username"])==0) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Username harus diisi.")));
    } else {
        $user_count = $db->getFieldValue("admin_users", array("COUNT(*) AS TOTAL"), array("username" => $_POST["username"]));
        if($user_count[0]["TOTAL"]>0) {
            $db->mysql_close();
            die(json_encode(array("success" => false, "message" => "Username sudah terpakai.")));
        }
    }
    
    if(strlen($_POST["password"])<6) {
        $db->mysql_close();
        die(json_encode(array("success" => false, "message" => "Password minimal 6 karakter atau lebih.")));
    }
    
    include $root."includes/php/MCrypt.php";
    $mcrypt = new MCrypt();
    $_password = $mcrypt->encrypt($_POST["password"]);
    
    //INSERT PENGGUNA
    $_POST["id_user_login"] = $db->getLastNumber("admin_users", "id");
    $id = $db->getLastNumber("admin_users", "id");
    $db->mysql_insert(
        "admin_users", 
        array(
            "id" => $_POST["id_user_login"],
            "username" => $_POST["username"],
            "nama" => $_POST["nama"],
            "password" => $_password,
            "id_sekolah" => $_POST["id_sekolah"],
            "tipe_user" => "G"
        )
    );
    
    $sql = "INSERT INTO user_menu_akses(id_user, id_menu, akses) SELECT '".$_POST["id_user_login"]."', id_menu, akses FROM grup_menu_akses WHERE id_grup='G'";
    $db->mysql_execute($sql);
    unset($_POST["username"]);    
    unset($_POST["password"]); 
    
    $_POST["id"] = $db->getLastNumber("guru", "id");
    $_POST["user_create"]  = $_SESSION[ADMIN_SESSION_NAME]["user"]["id"];
    $_POST["user_update"]  = $_SESSION[ADMIN_SESSION_NAME]["user"]["id"];
    $_POST["date_create"]  = date("Y-m-d H:i:s");
    $_POST["date_update"]  = date("Y-m-d H:i:s");
    unset($_POST["id_sekolah"]);    
    unset($_POST["nama_sekolah"]);    
    unset($_POST["id_jurusan"]);
    unset($_POST["nama_jurusan"]);
    unset($_POST["nama_subjurusan"]);
    $db->mysql_insert("guru", $_POST);
    
    $_SESSION["alert_success"] = "Tambah guru berhasil.";
    $db->mysql_close();
    die(json_encode(array("success" => true)));