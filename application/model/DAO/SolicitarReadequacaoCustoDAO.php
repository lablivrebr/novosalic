<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolicitarReadequacaoCustoDAO
 *
 * @author 01373930160
 */
class SolicitarReadequacaoCustoDAO extends GenericModelScriptCase {

    public function verificarreadequacao($idPronac)
    {
    	$sql = "SELECT 
					replace(CAST((SELECT SUM (qtItem * nrOcorrencia * vlUnitario)
						FROM SAC.dbo.tbPlanilhaAprovacao
						WHERE IdPRONAC = $idPronac AND stAtivo= 'S' AND tpplanilha != 'SR') AS money), ',', '.') AS totalAprovadoPlanilha
					,replace(CAST((SELECT SUM (qtItem * nrOcorrencia * vlUnitario)
						FROM SAC.dbo.tbPlanilhaAprovacao
						WHERE IdPRONAC = $idPronac AND stAtivo= 'N' AND tpacao != 'E' AND tpplanilha = 'SR') AS money), ',', '.') AS totalSolicitado
					,replace(CAST((SELECT SUM (qtItem * nrOcorrencia * vlUnitario)
						FROM SAC.dbo.tbPlanilhaAprovacao
						WHERE IdPRONAC = $idPronac AND stAtivo= 'N' AND tpacao = 'E' AND tpplanilha = 'SR') AS money), ',', '.') AS totalSolicitadoExcluido
					,replace((SELECT SAC.dbo.fnTotalAprovadoProjeto((SELECT AnoProjeto FROM SAC.dbo.Projetos WHERE IdPRONAC = $idPronac), (SELECT Sequencial FROM SAC.dbo.Projetos WHERE IdPRONAC = $idPronac))), ',', '.') AS totalAprovado";
        
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_ASSOC);

        return $db->fetchAll($sql);
    }

    public function buscarProjetos($idPronac) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pr' => 'Projetos'),
                        array(
                            'pr.IdPRONAC',
                            '(AnoProjeto+Sequencial) as nrpronac',
                            'pr.NomeProjeto',
                            'pr.CgcCpf'
                        )
                        , 'SAC.dbo'
                )
                ->joinInner(array('ar' => 'Area'),
                        "ar.Codigo = pr.Area",
                        array('Descricao as area'),
                        'SAC.dbo'
                )
                ->joinInner(array('a' => 'Agentes'),
                        "a.CNPJCPF = pr.CgcCpf",
                        array('idAgente'),
                        'Agentes.dbo'
                )
                ->joinInner(array('nm' => 'Nomes'),
                        "a.idAgente = nm.idAgente",
                        array('Descricao as Nome'),
                        "Agentes.dbo"
                )
                ->joinLeft(array('seg' => 'Segmento'),
                        "seg.Codigo = pr.Segmento",
                        array('Descricao as segmento'),
                        'SAC.dbo'
                )
                ->where('pr.IdPRONAC = ?', $idPronac);

        return $this->fetchAll($select);
    }

    public function buscarProdutos($idPronac) {

//        SELECT "pr"."IdPRONAC",
//"prep"."idPreprojeto",
//"ag"."TipoPessoa",
//"ag"."idAgente",
//"pd"."Descricao" AS "produto"
//FROM "SAC"."dbo"."Projetos" AS "pr"
//INNER JOIN "SAC"."dbo"."PreProjeto" AS "prep" ON prep.idPreProjeto = pr.idProjeto
//INNER JOIN "Agentes"."dbo"."Agentes" AS "ag" ON ag.idAgente = prep.idAgente
//LEFT JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto tpap on tpap.idpronac = pr.idpronac
//LEFT JOIN "SAC"."dbo"."tbPlanoDistribuicao" AS tpd ON tpd.idPedidoAlteracao = tpap.idPedidoAlteracao
//LEFT JOIN "SAC"."dbo"."Produto" AS "pd" ON tpd.idProduto = pd.Codigo WHERE (pr.IdPRONAC= '127152')
                
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->distinct();
        $slct->from(array('pr' => 'Projetos'),
                        array('IdPRONAC'),
                        "SAC.dbo"
                )
                ->joinInner(
                        array('prep' => 'PreProjeto'),
                        'prep.idPreProjeto = pr.idProjeto',
                        array('idPreprojeto'),
                        'SAC.dbo'
                )
                ->joinInner(
                        array('ag' => 'Agentes'),
                        'ag.idAgente = prep.idAgente',
                        array(
                            'TipoPessoa',
                            'idAgente'
                        ),
                        "Agentes.dbo"
                )
                ->joinLeft(
                        array('tpap' => 'tbPedidoAlteracaoProjeto'),
                        'tpap.IdPRONAC = pr.IdPRONAC',
                        array(),
                        'BDCORPORATIVO.scSAC'
                )
                ->joinLeft(
                        array('tpd' => 'tbPlanoDistribuicao'),
                        'tpd.idPedidoAlteracao = tpap.idPedidoAlteracao',
                        array(
                            'tpd.idProduto',
                            'tpd.idPlanoDistribuicao'),
                        'SAC.dbo'
                )
                ->joinLeft(
                        array('pd' => 'Produto'),
                        'tpd.idProduto = pd.Codigo',
                        array(
                            'pd.Descricao as produto'
                        ),
                        'SAC.dbo'
                )
                ->where('pr.IdPRONAC= ?', $idPronac)
                ->where('tpd.idProduto IS NOT NULL')
                ->where('pd.Descricao IS NOT NULL')
                ->where("tpd.tpAcao != 'E'");

//        xd($slct->query());
       return $this->fetchAll($slct);
    }

    public function buscarProdutosAprovados($idPronac) {



        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->distinct();
        $slct->from(array('pr' => 'Projetos'),
                        array('IdPRONAC'),
                        "SAC.dbo"
                )
                ->joinInner(
                        array('prep' => 'PreProjeto'),
                        'prep.idPreProjeto = pr.idProjeto',
                        array('idPreprojeto'),
                        'SAC.dbo'
                )
                ->joinInner(
                        array('ag' => 'Agentes'),
                        'ag.idAgente = prep.idAgente',
                        array(
                            'TipoPessoa',
                            'idAgente'
                        ),
                        "Agentes.dbo"
                )
                ->joinLeft(
                        array('tpap' => 'tbPedidoAlteracaoProjeto'),
                        'tpap.IdPRONAC = pr.IdPRONAC',
                        array(),
                        'BDCORPORATIVO.scSAC'
                )
                ->joinLeft(
                        array('pdp' => 'planoDistribuicaoProduto'),
                        'pdp.idProjeto = pr.idProjeto AND pdp.stPlanoDistribuicaoProduto = 1',
                        array(
                            'pdp.idProduto',
                            'pdp.idPlanoDistribuicao'),
                        'SAC.dbo'
                )
                ->joinLeft(
                        array('pd' => 'Produto'),
                        'pdp.idProduto = pd.Codigo',
                        array(
                            'pd.Descricao as produto'
                        ),
                        'SAC.dbo'
                )
                ->where('pr.IdPRONAC= ?', $idPronac)
                ->where('pd.Descricao IS NOT NULL');

    //xd($slct->assemble());
       return $this->fetchAll($slct);
    }

    public function buscarProdutosIndex($idPronac) {
        $sql = "SELECT  TOP 1   SAC.dbo.Projetos.IdPRONAC, AGENTES.dbo.Agentes.idAgente, AGENTES.dbo.Agentes.TipoPessoa, SAC.dbo.Produto.Descricao, SAC.dbo.Produto.Codigo AS idProduto
FROM         SAC.dbo.Projetos INNER JOIN
                      SAC.dbo.PreProjeto ON SAC.dbo.Projetos.idProjeto = SAC.dbo.PreProjeto.idPreProjeto INNER JOIN
                      AGENTES.dbo.Agentes ON SAC.dbo.PreProjeto.idAgente = AGENTES.dbo.Agentes.idAgente INNER JOIN
                      SAC.dbo.PlanoDistribuicaoProduto ON SAC.dbo.PreProjeto.idPreProjeto = SAC.dbo.PlanoDistribuicaoProduto.idProjeto INNER JOIN
                      SAC.dbo.Produto ON SAC.dbo.PlanoDistribuicaoProduto.idProduto = SAC.dbo.Produto.Codigo
WHERE     SAC.dbo.Projetos.IdPRONAC = $idPronac AND SAC.dbo.PlanoDistribuicaoProduto.stPlanoDistribuicaoProduto = 1 Order by SAC.dbo.Projetos.IdPRONAC Desc ";

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function buscarProdutosItens($idPronac=null, $idEtapa=null, $idProduto=null, $idaprovacao=null) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('tpa' => 'tbPlanilhaAprovacao'),
                        array('IdPRONAC',
                            'idProduto',
                            'idPlanilhaItem',
                            'idPlanilhaAprovacao',
                            'idUnidade',
                            'qtItem',
                            'CAST(dsJustificativa as text) as dsJustificativa',
                            'nrOcorrencia',
                            'vlUnitario',
                            'qtDias',
                            '(nrOcorrencia * vlUnitario * qtItem) Total',
                            'nrFonteRecurso',
                            'idUFDespesa as iduf',
                            'idMunicipioDespesa as idmun',
                            'tpAcao',
                            'idEtapa',
                            'stAtivo'
                        ),
                        "SAC.dbo"
                )
                ->joinInner(array('tpe' => 'tbPlanilhaEtapa'),
                        'tpa.idEtapa = tpe.idPlanilhaEtapa',
                        array('Etapa' => 'tpe.Descricao'),
                        "SAC.dbo")
                ->joinInner(array('tpu' => 'tbPlanilhaUnidade'),
                        'tpa.idUnidade = tpu.idUnidade',
                        array('Unidade' => 'tpu.Descricao'),
                        "SAC.dbo"
                )
                ->joinInner(array('tpi' => 'tbPlanilhaItens'),
                        'tpa.idPlanilhaItem = tpi.idPlanilhaItens',
                        array('tpi.Descricao as Item'),
                        "SAC.dbo"
                )
                ->joinInner(array('uf' => 'UF'),
                        'tpa.idUFDespesa = uf.idUF',
                        array('uf.Descricao as uf'),
                        "AGENTES.dbo"
                )
                ->joinInner(array('mun' => 'Municipios'),
                        'tpa.idMunicipioDespesa = mun.idMunicipioIBGE',
                        array('mun.Descricao as Municipio'),
                        'AGENTES.dbo'
                )
                ->joinInner(array('vf' => 'Verificacao'),
                        'tpa.nrFonteRecurso = vf.idVerificacao',
                        array('vf.Descricao as FonteRecurso')
                        , 'SAC.dbo'
                )
                ->where('tpa.stAtivo = ?', 'S');
        if (!empty($idPronac)) {
            $slct->where('tpa.IdPRONAC = ?', $idPronac);
        }
        if (!empty($idEtapa)) {
            $slct->where('tpa.idEtapa = ?', $idEtapa);
        }
        if (!empty($idProduto)) {
            $slct->where('tpa.idProduto= ?', $idProduto);
        }
        if (!empty($idaprovacao)) {
            $slct->where('tpa.idPlanilhaAprovacao= ?', $idaprovacao);
        }
        $slct->where('tpPlanilha <> ?', 'SR');
//        xd($slct->assemble());
        return $this->fetchAll($slct);
    }

    public function buscarProdutosItensInseridos($idPronac, $idEtapa = null, $idProduto = null, $idPlanilhaAprovacao = null) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('tpa' => 'tbPlanilhaAprovacao'),
                        array('IdPRONAC',
                            'idProduto',
                            'idPlanilhaItem',
                            'idPlanilhaAprovacao',
                            'idUnidade',
                            'qtItem',
                            'CAST(dsJustificativa as text) as dsJustificativa',
                            'nrOcorrencia',
                            'vlUnitario',
                            'qtDias',
                            '(nrOcorrencia * vlUnitario * qtItem) Total',
                            'nrFonteRecurso',
                            'idUFDespesa as iduf',
                            'idMunicipioDespesa as idmun',
                            'tpAcao',
                            'idEtapa',
                            'stAtivo'
                        ),
                        "SAC.dbo"
                )
                ->joinInner(array('tpe' => 'tbPlanilhaEtapa'),
                        'tpa.idEtapa = tpe.idPlanilhaEtapa',
                        array('Etapa' => 'tpe.Descricao'),
                        "SAC.dbo")
                ->joinInner(array('tpu' => 'tbPlanilhaUnidade'),
                        'tpa.idUnidade = tpu.idUnidade',
                        array('Unidade' => 'tpu.Descricao'),
                        "SAC.dbo"
                )
                ->joinInner(array('tpi' => 'tbPlanilhaItens'),
                        'tpa.idPlanilhaItem = tpi.idPlanilhaItens',
                        array('tpi.Descricao as Item'),
                        "SAC.dbo"
                )
                ->joinInner(array('uf' => 'UF'),
                        'tpa.idUFDespesa = uf.idUF',
                        array('uf.Descricao as uf'),
                        "AGENTES.dbo"
                )
                ->joinInner(array('mun' => 'Municipios'),
                        'tpa.idMunicipioDespesa = mun.idMunicipioIBGE',
                        array('mun.Descricao as Municipio'),
                        'AGENTES.dbo'
                )
                ->joinInner(array('vf' => 'Verificacao'),
                        'tpa.nrFonteRecurso = vf.idVerificacao',
                        array('vf.Descricao as FonteRecurso')
                        , 'SAC.dbo'
                )
                ->where('tpa.stAtivo = ?', 'N')
                ->where('tpa.tpPlanilha = ?', 'SR')
                ->where('tpa.IdPRONAC = ?', $idPronac)
                ->where('tpa.idPedidoAlteracao is not null');

		if (!empty($idProduto) && $idProduto != 0) :
			$slct->where('tpa.idProduto = ?', $idProduto);
		endif;

		if (!empty($idEtapa)) :
			$slct->where('tpa.idEtapa = ?', $idEtapa);
		endif;

		if (!empty($idPlanilhaAprovacao)) :
			$slct->where('tpa.idPlanilhaAprovacao = ?', $idPlanilhaAprovacao);
		endif;

//        xd($slct->query());
        return $this->fetchAll($slct);
    }

    public static function inserirCopiaPlanilha($idPronac, $idPedidoAlteracao) {
        $sql = "insert into SAC.dbo.tbPlanilhaAprovacao
                    SELECT
                    'tpPlanilha' = 'SR',
                    GETDATE(),
                    idPlanilhaProjeto,
                    idPlanilhaProposta,
                    IdPRONAC,
                    idProduto,
                    idEtapa,
                    idPlanilhaItem,
                    dsItem,
                    idUnidade,
                    qtItem,
                    nrOcorrencia,
                    vlUnitario,
                    qtDias,
                    tpDespesa,
                    tpPessoa,
                    nrContraPartida,
                    nrFonteRecurso,
                    idUFDespesa,
                    idMunicipioDespesa,
                    dsJustificativa,
                    idAgente,
                    idPlanilhaAprovacao,
                    $idPedidoAlteracao,
                    tpAcao,
                    idRecursoDecisao,
                    'stAtivo' = 'N'
                    FROM         SAC.dbo.tbPlanilhaAprovacao
                    WHERE     (IdPRONAC = $idPronac) AND (stAtivo = 'S') AND tpPlanilha='CO'

                    ";
//        die($sql);
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->query($sql);
    }

    public static function inserirProdutosItens($post, $idPedido, $idProduto = null) {
        try {
            $idCodigoProduto = isset($post['idProduto']) ? $post['idProduto'] : 0;
            $item = isset($post['item']) ? $post['item'] : 0;
            $sqlItem = "SELECT Descricao FROM SAC.dbo.tbPlanilhaItens WHERE SAC.dbo.tbPlanilhaItens.idPlanilhaItens = $item";
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_ASSOC);
            $dscItem = $db->fetchRow($sqlItem);

            $descricaoItem = $dscItem['Descricao'];

            $dados = array('tpPlanilha' => 'SR',
                'dtPlanilha' => date('Y-m-d H:i:s'),
                'IdPRONAC' => $post['idpronac'],
                'idProduto' => $idCodigoProduto,
                'idEtapa' => $post['etapa'],
                'idPlanilhaItem' => $item,
                'dsItem' => $descricaoItem,
                'idUnidade' => $post['unidade'],
                'qtItem' => $post['qtd'],
                'nrOcorrencia' => $post['ocorrencia'],
                'vlUnitario' => $post['vlUnitario'],
                'qtDias' => $post['dias'],
                'tpDespesa' => 0,
                'tpPessoa' => $post['idTipoPessoa'],
                'nrContraPartida' => 0,
                'nrFonteRecurso' => $post['fonte'],
                'idUFDespesa' => $post['uf'],
                'idMunicipioDespesa' => $post['municipio'],
                'dsJustificativa' => TratarString::escapeString($post['justificativa']),
                'idAgente' => $post['idAgente'],
                'idPedidoAlteracao' => $idPedido,
                'tpAcao' => $post['acao'],
                'stAtivo' => "N"
            );

            $db->insert('SAC.dbo.tbPlanilhaAprovacao', $dados);
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Exception $e) {
            return "Erro:" . $e->getMessage();
        }
    }

    public static function inserirNovoProduto($dados) {
        $db = Zend_Registry::get('db');
        $db->insert('SAC.dbo.tbPlanilhaAprovacao', $dados);
    }

    public function inserirPedido($dados) {

        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $db->insert('BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto', $dados);
        return $db->lastInsertId();
    }

    public static function alterarPedidoAlterado($post) {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $db->update('BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto', $dados, $where);
    }

    public static function atualizaPedidoAlteracao($dados, $idPedido) {
        try {
            $where = " idPedidoAlteracao = $idPedido";

            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
            $db->update('BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto', $dados, $where);
        } catch (Exception $e) {
            return 'ERRO:' . $e->getMessage();
        }
    }

    public static function atualizaPedidoAlteracaoStatusTemporario($post, $idPedido) {

        $idPronac = $post['idpronac'];
        $idAgente = $post['idAgente'];
        $acao = $post['acao'];

        $sql = "UPDATE    BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto SET
                    stPedidoAlteracao = 'T' WHERE IdPRONAC =  $idPronac and idSolicitante =  $idAgente and idPedidoAlteracao = $idPedido ";




        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function atualizaPedidoAlteracaoStatusAlterado($post, $idPedido) {

        $idPronac = $post['idpronac'];
        $idAgente = $post['idAgente'];
        $acao = $post['acao'];

        $sql = "UPDATE    BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto SET
                    stPedidoAlteracao = 'A' WHERE IdPRONAC =  $idPronac and idSolicitante =  $idAgente and idPedidoAlteracao = $idPedido ";




        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function controlaStatus($status, $idPronac) {



        $sql = "UPDATE    BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto SET
                    stPedidoAlteracao = '$status' WHERE IdPRONAC =  $idPronac ";




        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function atualizaItem($dados, $where) {
        try {
            $db = Zend_Registry::get('db');
            $db->update('SAC.dbo.tbPlanilhaAprovacao', $dados, $where);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function buscaUltimaPlanilhaAprovada($where) {
        $sql = "SELECT TOP 1 idPlanilhaAprovacao FROM SAC.dbo.tbPlanilhaAprovacao
                WHERE $where ORDER BY  idPlanilhaAprovacao DESC

        ";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function alterarItem($dados, $where) {
        try {
            $db = Zend_Registry::get('db');
            $db->update('SAC.dbo.tbPlanilhaAprovacao', $dados, $where);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function atualizaPedidoAlteracaoIndex($idPronac, $idPedido, $idSolicitante, $acao) {


        $sql = "UPDATE    BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto SET
                    stPedidoAlteracao = '$acao' WHERE IdPRONAC =  $idPronac and idSolicitante =  $idSolicitante and idPedidoAlteracao = $idPedido ";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function inserirPedidoTipo($dados) {
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
            $db->insert('BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao', $dados);
        } catch (Zend_Exception $e) {
            die('erro:' . $e->getMessage());
        }
    }

    public static function atualizaPedidoTipoAlteracao($idPedidoAlteracao, $justificativa) {


        $sql = "update BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao set dsJustificativa = '$justificativa'
                where idPedidoAlteracao = $idPedidoAlteracao and tpAlteracaoProjeto = 10    ";





        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function verificaPedidoTipoAlteracao($idPedidoAlteracao, $tpalteracaoprojeto) {
        $sql = "select 1 from BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao WHERE idPedidoAlteracao = '$idPedidoAlteracao' and tpAlteracaoProjeto = $tpalteracaoprojeto";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function atualizaPedidoAlteracaoTipo($post, $idPedido) {

        $idPronac = $post['idpronac'];
        $idAgente = $post['idAgente'];
        $acao = $post['acao'];

        $sql = "UPDATE    BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto SET
                    stPedidoAlteracao = 'T' WHERE IdPRONAC =  $idPronac and idSolicitante =  $idAgente and idPedidoAlteracao = $idPedido ";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function verificaPedidoAlteracao($idPronac) {
    
        $sql = "Select idPedidoAlteracao, stPedidoAlteracao  from BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto
                    WHERE IdPRONAC = $idPronac order by idPedidoAlteracao Desc ";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_ASSOC);
        return $db->fetchRow($sql);
    }

    public static function verificaItem($post) {
        $etapa = $post['etapa'];
        $item = $post['item'];

        if (!isset($post['idProduto'])) {
            $idProduto = 0;
        } else {
            $idProduto = $post['idProduto'];
        }
        $sql = "select * from SAC.dbo.tbPlanilhaAprovacao where idEtapa = $etapa  and idProduto = $item and idPlanilhaItem = $idProduto and tpPlanilha = 'SR'";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    public static function verificaTipoAcao($idPronac) {
        $sql = "SELECT TOP 1    tpAcao
                FROM         SAC.dbo.tbPlanilhaAprovacao where IdPRONAC = $idPronac order by idPlanilhaAprovacao desc";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    public static function buscaIdPedidoAlteracao($idPronac) {

        $sql = "select MAX(idPedidoAlteracao) as idpedidoalteracao from  BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto where idpronac = $idPronac";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function buscarPlanilhaAprovacao($idPronac, $idCodigoProduto = null) {
        $buscarPlanilhaAprovacao = $this->select();
        $buscarPlanilhaAprovacao->setIntegrityCheck(false);
        $buscarPlanilhaAprovacao->from(
                                        array('tpa' => 'tbPlanilhaAprovacao'),
                                        array(
                                            'tpa.IdPRONAC',
                                            'tpa.idProduto',
                                            'tpa.idPlanilhaAprovacao'
                                        ),
                                        'SAC.dbo'
                                        )
                                        ->where('IdPRONAC = ?', $idPronac);
        if (!empty($idCodigoProduto)) {
            $buscarPlanilhaAprovacao->where('idProduto= ?', $idCodigoProduto);
        }

        return $this->fetchAll($buscarPlanilhaAprovacao);
    }

    public function buscarProdutoAprovacao($idPronac) {
        $buscarPlanilhaAprovacao = $this->select();
        $buscarPlanilhaAprovacao->setIntegrityCheck(false);
        $buscarPlanilhaAprovacao->from(

                                        array('pdp' => 'PlanoDistribuicaoProduto'),
                                        array(
                                            'pr.IdPRONAC',
                                            'ag.TipoPessoa',
                                            'ag.idAgente',
                                            'pdp.idProduto',
                                            'pd.Descricao AS produto'
                                        ),
                                        'SAC.dbo'
                                        )
                                        ->joinInner(
                                                array('pr' => 'Projetos'),
                                                'pdp.idProjeto = pr.idProjeto',
                                                array(),
                                                'SAC.dbo'
                                        )
                                        ->joinInner(
                                                array('pd' => 'Produto'),
                                                'pdp.idProduto = pd.Codigo',
                                                array(),
                                                'SAC.dbo'
                                        )

                                        ->joinInner(
                                                array('ag' => 'Agentes'),
                                                'ag.CNPJCPF = pr.CgcCpf',
                                                array(),
                                                'Agentes.dbo'
                                        )
                                        ->where('pr.IdPRONAC = ?', $idPronac)
                                        ->where('pdp.stPlanoDistribuicaoProduto = ?', 1);

//        xd($buscarPlanilhaAprovacao->query());
        return $this->fetchAll($buscarPlanilhaAprovacao);
    }

        public function buscarProdutoAprovacaoSemProposta($idPronac) {
        $buscarPlanilhaAprovacao = $this->select();
        $buscarPlanilhaAprovacao->setIntegrityCheck(false);
        $buscarPlanilhaAprovacao->from(

                                        array('tpd' => 'tbPlanoDistribuicao'),
                                        array(
                                            'tpd.idProduto',
                                         ),
                                        'SAC.dbo'
                                        )
                                        ->joinInner(
                                                array('prd' => 'Produto'),
                                                'tpd.idProduto = prd.Codigo',
                                                array(
                                                    'prd.Codigo',
                                                    'prd.Descricao as produto',
                                                ),
                                                'SAC.dbo'
                                        )
                                        ->joinInner(
                                                array('tpa' => 'tbPedidoAlteracaoProjeto'),
                                                'tpd.idPedidoAlteracao = tpa.idPedidoAlteracao',
                                                array(
                                                    'tpa.IdPRONAC'

                                                ),
                                                'BDCORPORATIVO.scSac'
                                        )
                                        ->joinInner(
                                                array('pr' => 'Projetos'),
                                                'tpa.IdPRONAC = pr.IdPRONAC',
                                                array(),
                                                'SAC.dbo'
                                        )
                                        ->joinInner(
                                                array('ag' => 'Agentes'),
                                                'ag.CNPJCPF = pr.CgcCpf',
                                                array(
                                                    
                                                    'ag.TipoPessoa',
                                            'ag.idAgente',
                                                ),
                                                'Agentes.dbo'
                                        )
                                        
                                        ->where('tpa.IdPRONAC = ?', $idPronac);

      
        return $this->fetchAll($buscarPlanilhaAprovacao);
    }

    public function buscarEtapa($tipoproduto) {
        $slctEtapa = $this->select();
        $slctEtapa->setIntegrityCheck(false);
        $slctEtapa->from('tbPlanilhaEtapa',
                array(),
                'SAC.dbo'
        );
        $slctEtapa->where('tpCusto= ?', $tipoproduto);
        return $this->fetchAll($slctEtapa);
    }
    
        public function buscarItensCadastrados($idPronac) {
        $slctItens = $this->select();
        $slctItens->setIntegrityCheck(false);
        $slctItens->from(
                        array('tpa' => 'tbPlanilhaAprovacao'),
                        array('*'),
                        'SAC.dbo'
                        )
                ->joinInner(
                        array('pr' => 'Projetos'),
                        'tpa.idPronac = pr.IdPRONAC',
                        array(''),
                        'SAC.dbo'
                )
                ->joinInner(
                        array('prep' => 'PreProjeto'),
                        'prep.idPreProjeto = pr.idProjeto',
                        array('idPreprojeto'),
                        'SAC.dbo'
                )
                ->joinInner(
                        array('ag' => 'Agentes'),
                        'ag.idAgente = prep.idAgente',
                        array(
                            'TipoPessoa',
                            'idAgente'
                        ),
                        "Agentes.dbo"
                );
        //$slctItens->where("tpPlanilha = ?", 'SR');
        $slctItens->where('tpa.idPronac = ?', $idPronac);
        //xd($slctItens->assemble());
        return $this->fetchAll($slctItens);
    }

    public static function buscarItens($idEtapa, $idproduto = null) {
        $sql = " select distinct tpi.idPlanilhaItens,
                tpi.Descricao
                from SAC.dbo.tbPlanilhaItens tpi
                INNER JOIN SAC..tbItensPlanilhaProduto tbipp on tbipp.idPlanilhaItens = tpi.idPlanilhaItens
                where idPlanilhaEtapa = $idEtapa  ";

        if (!empty($idproduto)) {
            $sql .= " and tbipp.idProduto = $idproduto";
        }

        $sql .= " order by 2 asc";



        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        return $db->fetchAll($sql);
    }

    public function buscarFonteRecurso() {
        $fonte = $this->select();
        $fonte->setIntegrityCheck(false);
        $fonte->from(array('ver' => 'Verificacao'),
                        array(
                            'ver.idVerificacao',
                            '(ltrim(ver.Descricao)) as VerificacaoDescricao'
                        ),
                        'SAC.dbo')
                ->joinInner(
                        array('tp' => 'Tipo'),
                        'ver.idTipo = tp.idTipo',
                        array(),
                        'SAC.dbo'
                )
                ->where('tp.idTipo = ?', '5');
        return $this->fetchAll($fonte);
    }

    public function buscarUnidade() {
        $sql = "select idUnidade, Sigla, Descricao  from SAC.dbo.tbPlanilhaUnidade";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function buscarUF() {
        $sql = "select idUF,Sigla,Descricao,Regiao from AGENTES.dbo.UF order by 3 asc";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public static function buscarMunicipio($iduf) {
        $sql = "select idMunicipioIBGE, idUFIBGE, Descricao from AGENTES.dbo.Municipios where idUFIBGE = $iduf";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        return $db->fetchAll($sql);
    }

    public function verificarPlanilhaCriada($idpronac, $tpplanilha) {
        $verifica = $this->select();
        $verifica->setIntegrityCheck(false);
        $verifica->from('tbPlanilhaAprovacao',
                        array(),
                        'SAC.dbo')
                ->where('idPRONAC = ?', $idpronac)
                ->where('tpPlanilha = ?', $tpplanilha);
        return $this->fetchAll($verifica)->current();
    }

}
?>


