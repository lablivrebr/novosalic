<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contratoxagentes
 *
 * @author 01610881125
 */
class Contratoxagentes extends GenericModel {
    protected $_banco   = 'bdcorporativo';
    protected $_name    = 'tbContratoxAgentes';
    protected $_schema  = 'scSAC';

    public function inserirContratoxAgentes($data){
        $insert = $this->insert($data);
        return $insert;
    }

    public function alterarContratoxAgentes($data, $where){
        $update = $this->update($data, $where);
        return $update;
    }

    public function deletarContratoxAgentes($where){
        $delete = $this->delete($where);
        return $delete;
    }

    public function buscarAgentes($where){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('cxa'=>$this->_schema.'.'.$this->_name),
                        array('cxa.idAgente')
                      );

        $select->joinInner(
                            array('ag'=>'Agentes'),
                            'cxa.idAgente = ag.idAgente',
                            array('ag.CNPJCPF','ag.TipoPessoa'),
                            'AGENTES.dbo'
                           );
        $select->joinInner(
                            array('nm'=>'Nomes'),
                            'nm.idAgente = ag.idAgente',
                            array('nm.Descricao'),
                            'AGENTES.dbo'
                           );

        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        return $this->fetchAll($select);
    }
}
?>
