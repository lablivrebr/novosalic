<?php

/**
 * Modelo que representa a tabela SAC.dbo.tbComunicados 
 *
 * @author Danilo Lisboa
 */
class tbComunicados extends GenericModel {
   
    protected  $_banco  = 'SAC';
    protected  $_schema = 'dbo';
    protected  $_name   = 'tbComunicados';

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function listarComunicados($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $count=false) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array("c"=>$this->_name),
                array("idComunicado", 
					  "Comunicado",
					  "idSistema", 
					  "stOpcao", 
					  "stEstado", 
					  "dtInicioVigencia", 
					  "dtTerminoVigencia",
					  "CONVERT(CHAR(10),dtInicioVigencia,103) AS dtInicioVigenciaPT", 
					  "CONVERT(CHAR(10),dtTerminoVigencia, 103) AS dtTerminoVigenciaPT")
		);
                
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

		if($count){
            $slct2 = $this->select();
            $slct2->setIntegrityCheck(false);
            $slct2->from(
                            array("c"=>$this->_name),
                            array('total'=>"count(*)")
                         );
            //adiciona quantos filtros foram enviados
            foreach ($where as $coluna => $valor) {
                $slct2->where($coluna, $valor);
            }

            //xd($slct2->__toString());
            $rs = $this->fetchAll($slct2)->current();
            if($rs){ return $rs->total; }else{ return 0; }
        }
        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
        
        //xd($slct->assemble());
        
        return $this->fetchAll($slct);
    }



}