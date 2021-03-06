<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mantersecretarioorgao
 *
 * @author Tiago
 */
class MantersecretarioorgaoController extends GenericControllerNew {

    public function init() {
        // verifica as permiss�es
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 97; // Gestor Salic

        parent::perfil(1, $PermissoesGrupo);

        parent::init();

        // cria a sessao com o grupo ativo
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $this->codGrupo = $GrupoAtivo->codGrupo;
    }

    public function indexAction() {

        $tbOrgao = New Orgaos();
        $buscaOrgaos = $tbOrgao->buscar(array(), array('Sigla'));

        if (!empty($buscaOrgaos[0])) {
            $this->view->orgaos = $buscaOrgaos;
        }
    }

    public function buscarsecretarioAction() {

        $orgao = $this->_request->getParam("orgao");
        $orgao = (int) $orgao;              

        $tbSecretario = New tbSecretario();
        $buscarOrgaoSecretario = $tbSecretario->buscar(array('idOrgao = ?' => $orgao));

        if (!empty($buscarOrgaoSecretario[0])) {
            $result['existe'] = true;
            $result['nmSecretario'] = utf8_encode($buscarOrgaoSecretario[0]->nmSecretario);
            $result['dsCargo'] = utf8_encode($buscarOrgaoSecretario[0]->dsCargo);
            echo json_encode($result);
            exit();
        } else {
            $result['existe'] = false;
            echo json_encode($result);
            exit();
        }
    }

    public function cadastrarsecretarioAction() {

        $orgao = $this->_request->getParam("orgao");
        $nomeSecretario = $this->_request->getParam("nomeSecretario");
        $cargo = $this->_request->getParam("cargo");
        
        $tbSecretario = New tbSecretario();
        $buscarOrgaoSecretario = $tbSecretario->buscar(array('idOrgao = ?' => $orgao));

        if (!empty($buscarOrgaoSecretario[0])) { //atualiza orgaosecretario
            $rsOrgaoSecretario = $tbSecretario->buscar(array("idOrgao = ?" => $orgao))->current();
            $rsOrgaoSecretario->idOrgao = $orgao;
            $rsOrgaoSecretario->nmSecretario = $nomeSecretario;
            $rsOrgaoSecretario->dsCargo = $cargo;
            $rsOrgaoSecretario->save();
            $acao = "Altera��o realizada";
        } else {
            $dados = array(//insere orgaosecretario
                'idOrgao' => $orgao,
                'nmSecretario' => $nomeSecretario,
                'dsCargo' => $cargo);

            $salvarOrgaoSecretario = $tbSecretario->inserir($dados);
            $acao = "Cadastro realizado";
        }
        parent::message("{$acao} com sucesso! ", "mantersecretarioorgao/index?orgao=".$orgao, "CONFIRM");
    }
}

?>
