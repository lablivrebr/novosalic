<?php

/**
 * DAO tbRecursoXPlanilhaAprovacao
 * @author emanuel.sampaio - Politec
 * @since 18/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright � 2011 - Minist�rio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class tbRecursoXPlanilhaAprovacao extends GenericModel
{

    protected $_banco = "SAC";
    protected $_schema = "dbo";
    protected $_name = "tbRecursoXPlanilhaAprovacao";

    /**
     * M�todo para buscar o(s) recursos(s)
     * @access public
     * @param void
     * @return object
     */
    public function buscarDados()
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from($this);

        return $this->fetchAll($select);
    }

    /**
     * M�todo para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o �ltimo id cadastrado)
     */
    public function cadastrarDados($dados)
    {
        return $this->insert($dados);
    }

    /**
     * M�todo para excluir
     * @access public
     * @param integer $idPlanilha (excluir todos os recursos vinculados a planilha de aprova��o)
     * @param integer $idRecurso (excluir um determinado recurso)
     * @return integer (quantidade de registros exclu�dos)
     */
    public function excluirDados($idPlanilha = null, $idRecurso = null)
    {
        // exclui todos os recursos vinculados a planilha de aprova��o
        if (!empty($idPlanilha)) {
            $where = "idPlanilhaAprovacao = " . $idPlanilha;
        }

        // exclui um determinado recurso
        else if (!empty($idRecurso)) {
            $where = "idRecurso = " . $idRecurso;
        }

        return $this->delete($where);
    }

}
