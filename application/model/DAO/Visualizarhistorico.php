<?php
class Visualizarhistorico extends Zend_Db_Table
{

       	protected $_name    = 'SAC.dbo.Projetos';

    public function buscar($sql)   
    {
       	//echo $sql . "<br>";
       	$db = Zend_Registry :: get('db');
		$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		$resultado = $db->fetchAll($sql);
		return $resultado;
	} 
	
    public function inserirMensagem($PRONAC,$componenteComissao,$mensagem)   
    {
       	$sql = "INSERT INTO BDCORPORATIVO.scSAC.tbMensagemProjeto (idPRONAC, idRemetente, idDestinatario, dtEncaminhamento, dsMensagem, stAtivo)
					        VALUES ($PRONAC, 75, $componenteComissao, GETDATE(), '$mensagem', 'A')"; 
       	//echo $sql; die();
       	$db = Zend_Registry :: get('db');
		$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		$resultado = $db->query($sql);
		return $resultado;
	} 
	
	public function buscaProjeto($pronac)
	{
		$sql = "SELECT 
								IdPRONAC, 
								NomeProjeto, 
								CONVERT(CHAR(10),DtProtocolo,103) as Data 
						FROM SAC.dbo.Projetos WHERE 
								IdPRONAC = " . $pronac . " ";
		
		$db = Zend_Registry :: get('db');
		$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		$resultado = $db->fetchAll($sql);
		return $resultado;
	}
	
	public function buscaHistorico($pronac)
	{
		$historico = "select 
    CONVERT(CHAR(10),dtEncaminhamento,103) as dtEncaminhamento, 
        idRemetente, 
        remetente.Descricao as nmRemetente, 
        idDestinatario, 
        destinatario.Descricao as nmDestinatario,
        dsMensagem
from BDCORPORATIVO.scSAC.tbmensagemprojeto mp
left join Agentes.dbo.Nomes remetente on remetente.idAgente = mp.idRemetente
left join Agentes.dbo.Nomes destinatario on destinatario.idAgente = mp.idDestinatario
left join SAC.dbo.Projetos Pr on Pr.IdPRONAC = mp.idPRONAC
 where mp.stAtivo = 'A' and Pr.IdPRONAC = " . $pronac . "";
					
		$db = Zend_Registry :: get('db');
		$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		$resultado = $db->fetchAll($historico);
		return $resultado;
	}
	
	public function buscaConselheiro()
	{
		$conselheiro = "SELECT 
								N.Descricao as nomeConselheiro, 
								N.idAgente as agente
						FROM AGENTES.dbo.Agentes A,
								AGENTES.dbo.Nomes N, 
								AGENTES.dbo.Visao V 
				 		WHERE V.idAgente = A.idAgente 
						AND V.Visao = 210 
						AND N.idAgente = A.idAgente
						ORDER BY N.Descricao";
						
		$db = Zend_Registry :: get('db');
		$db->setFetchMode(Zend_DB :: FETCH_OBJ);
		$resultado = $db->fetchAll($conselheiro);
		return $resultado;
	}
	
	

	
	
}		// fecha class
				