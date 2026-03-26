<?php
// include('security.php');
include('./lib/my_functions.php');



?>
<!DOCTYPE html>
<html lang="en">

<head>


            <!--PWA-FACTORY.COM-->
            <!--ADD THE FILES IN YOUR ROOT FOLDER & INCLUDE THIS IN YOUR HTML HEAD-->
                <link rel="manifest" href="manifest2.json">
                <script>
                    if ('serviceWorker' in navigator) {
                        window.addEventListener("load", () => {
                            navigator.serviceWorker.register('service-worker2.js').then(function(registration) {
                                console.log('ServiceWorker registered');
                              }).catch(function(err) {
                                console.log('ServiceWorker error: ', err);
                              });
                        })
                    }
                </script>
            <!--PWA-FACTORY.COM-->




  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="Pedro Díaz">
  <link rel="icon" href="data:,">


  <title>SINCRM3</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <script src="https://kit.fontawesome.com/83d95dbe8d.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/sb-admin-2-2.css" rel="stylesheet">
<!-- Custom styles for this page -->
  <link href="vendor/datatables/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">