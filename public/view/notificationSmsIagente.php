<?php

$dados['id'] = filter_input(INPUT_GET, 'codigosms', FILTER_VALIDATE_INT);
$dados['status'] = filter_input(INPUT_GET, 'status', FILTER_DEFAULT);
$dados['mensagem'] = filter_input(INPUT_GET, 'mensagem', FILTER_DEFAULT);
$dados['celular'] = filter_input(INPUT_GET, 'celular', FILTER_DEFAULT);

$read = new \ConnCrud\Read();
$read->exeRead("smsIagente", "WHERE id = :id", "id={$dados['id']}");
if($read->getResult() && $dados['status'] !== $read->getResult()[0]['status']){
    $d = new \EntityForm\Dicionario("smsIagente");
    $d->setData($dados);
    $d->save();
}