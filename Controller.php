<?php

require('AccessToken.php');
require('DocumentList.php');
require('ViewDocument.php');
require('ManualSignDocument.php');
require('UploadDocument.php');
require('AutoSignDocument.php');
require('StoreAPIKey.php');
require('DeleteDocument.php');
require('UploadSignature.php');
require('VerifyDocument.php');


if (!empty($_POST['documentList'])) {
    $documentList = new DocumentList();
    if (!empty($_POST['signed'])) {
        $documentList->getDocumentList("4");
    }else {
        $documentList->getDocumentList("1");
    }
} elseif (!empty($_POST['previewDoc'])) {
    $viewDocument = new ViewDocument();
    $viewDocument->getViewUrl();
} elseif (!empty($_POST['manualSignDoc'])) {
    $manualSignDocument = new ManualSignDocument();
    $manualSignDocument->getSignUrl();
} elseif (!empty($_POST['uploadDoc'])) {
    $uploadDocument = new UploadDocument();
    $uploadDocument->uploadDoc();
} elseif (!empty($_POST['autoSignDoc'])) {
    $autoSignDocument = new AutoSignDocument();
    $autoSignDocument->autoSign();
} elseif (!empty($_POST['uploadKey'])) {
    $storeAPIKey = new StoreAPIKey();
    $storeAPIKey->storeKey();
} elseif (!empty($_POST['deleteDoc'])) {
    $deleteDocument = new DeleteDocument();
    $deleteDocument->deleteDoc();
} elseif (!empty($_POST['uploadSignature'])) {
    $uploadSignature = new UploadSignature();
    $uploadSignature->uploadSignImg();
} elseif (!empty($_POST['verifyDoc'])) {
    $verifyDocument = new VerifyDocument();
    $verifyDocument->verifyDoc();
}


?>