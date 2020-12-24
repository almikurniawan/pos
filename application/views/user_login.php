<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="POS Kang Bayar">
    <meta name="author" content="facebook.com/almikur">
    <title>Kang Bayar</title>

    <link href="<?= base_url("/assets/bootstrap/css/bootstrap.min.css")?>" rel="stylesheet">
    <script src="<?= base_url("/assets/kendo/js/jquery.min.js")?>"></script>
    <script src="<?= base_url("/assets/bootstrap/js/bootstrap.min.js")?>"></script>
    <style>
        body{
            background: #4A89DC;
        }
        .login-container {
            margin-top: 5%;
            margin-bottom: 5%;
        }

        .login-form-1 {
            padding: 5%;
            box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 26px 0 rgba(0, 0, 0, 0.19);
        }

        .login-form-1 h3 {
            text-align: center;
            color: #333;
        }

        .login-form-2 {
            padding: 5%;
            background: #0254bd;
            box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 26px 0 rgba(0, 0, 0, 0.19);
        }

        .login-form-2 h3 {
            text-align: center;
            color: #fff;
        }

        .login-container form {
            padding: 10%;
        }

        .btnSubmit {
            width: 50%;
            border-radius: 1rem;
            padding: 1.5%;
            border: none;
            cursor: pointer;
        }

        .login-form-1 .btnSubmit {
            font-weight: 600;
            color: #fff;
            background-color: #0062cc;
        }

        .login-form-2 .btnSubmit {
            font-weight: 600;
            color: #0062cc;
            background-color: #fff;
        }

        .login-form-2 .ForgetPwd {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        .login-form-1 .ForgetPwd {
            color: #0062cc;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="container-fluid login-container">
        <div class="row justify-content-md-center">
            <div class="col-lg-4 login-form-2">
                <h3>Login</h3>
                <form method="post" action="<?= base_url("login/do_login")?>">
                    <div class="form-group">
                        <input type="text" name="user_name" class="form-control" placeholder="Username" value="" />
                    </div>
                    <div class="form-group">
                        <input type="password" name="user_password" class="form-control" placeholder="Password"
                        value="" />
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btnSubmit" value="Login" />
                    </div>
                </form>
                <?php if($this->session->flashdata('warning')){?>
                    <div class="alert alert-danger" role="alert">
                        <?= $this->session->flashdata('warning')?>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
</body>

</html>