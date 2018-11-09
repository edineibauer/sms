<?php

try {
    $sms = new \IagenteSms\Sms();
    $sms->setCelular($dados['celular']);
    $sms->setMensagem($dados['mensagem']);
    $sms->setId($dados['id']);
    $sms->envia();
} catch (Exception $e) {
    //Não faz nada com possíveis erros
}