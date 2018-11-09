<?php

namespace Sms;

class Sms
{
    private $celular;
    private $mensagem;
    private $id;
    private $error;

    public function __construct()
    {
        if (!defined('SMSIAGENTEUSER') || !defined('SMSIAGENTESENHA'))
            $this->error = "Usuário e/ou Senha da Iagentesms não informados, informar nas configurações para uso de SMS.";

        $this->celular = [];
    }

    /**
     * @param mixed $celular
     */
    public function setCelular(string $celular)
    {
        if (is_numeric($celular) && strlen($celular) > 7 && strlen($celular) < 16)
            $this->celular[] = $celular;
    }

    /**
     * @param array $celular
     */
    public function setCelulares(array $celular = [])
    {
        foreach ($celular as $item)
            $this->setCelular($item);
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Alias para Envia
     */
    public function send()
    {
        $this->envia();
    }

    /**
     * Dispara o Envio de SMS
     */
    public function envia()
    {
        if (empty($this->error)) {

            if (!$this->id)
                $this->id = $this->getLastId();

            if (!$this->mensagem)
                $this->error = "Mensagem de Texto Ausente";

            if (empty($this->celular))
                $this->error = "Número de Celular Ausente";

            if (empty($this->error)) {
                if (count($this->celular) === 1)
                    $this->singleSend();
                else
                    $this->multSend();
            }
        }
    }

    /**
     * Envia mensagem para um número
     */
    private function singleSend()
    {
        try {
            $response = \Helpers\Helper::postRequest("http://www.iagentesms.com.br/webservices/http.php", [
                "metodo" => "envio",
                "usuario" => SMSIAGENTEUSER,
                "senha" => SMSIAGENTESENHA,
                "celular" => $this->celular[0],
                "mensagem" => substr($this->mensagem, 0, 160),
                "data" => date("d/m/Y H:i:s"),
                "codigosms" => $this->id
            ]);

            if ($response !== "OK")
                $this->error = $response;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Envia mensagem para multiplos números
     */
    private function multSend()
    {
        try {
            $response = \Helpers\Helper::postRequest("http://www.iagentesms.com.br/webservices/http.php", [
                "metodo" => "envio",
                "usuario" => SMSIAGENTEUSER,
                "senha" => SMSIAGENTESENHA,
                "celular" => implode(', ', $this->celular),
                "mensagem" => substr($this->mensagem, 0, 160),
                "data" => date("d/m/Y H:i:s")
            ]);

            if ($response !== "OK")
                $this->error = $response;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Obtém o status do SMS
     * @return string
     */
    public function getStatus()
    {
        if (empty($this->error) && $this->id) {
            $result = \Helpers\Helper::postRequest("http://www.iagentesms.com.br/webservices/http.php", [
                "metodo" => "consulta",
                "usuario" => SMSIAGENTEUSER,
                "senha" => SMSIAGENTESENHA,
                "codigosms" => $this->id
            ]);

            if (!preg_match('/^ERRO/i', $result)) {
                return $result;
            } else {
                $this->error = $result;
                return "";
            }
        } elseif (empty($this->error)) {
            $this->error = "id não informado";
        }

        return "";
    }

    /**
     * @return int
     */
    private function getLastId(): int
    {
        $read = new \ConnCrud\Read();
        $read->exeRead("smsIagente", "WHERE ORDER BY id DESC LIMIT 1");
        return $read->getResult() ? ($read->getResult()[0]['id'] + 1) : 1;
    }
}