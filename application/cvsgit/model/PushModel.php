<?php
namespace CVS;

require_once APPLICATION_DIR . 'cvsgit/model/CvsGitModel.php';

class PushModel extends CvsGitModel {

  private $aArquivos;
  private $sTitulo;

  public function adicionar(Array $aArquivos) {
    $this->aArquivos = $aArquivos;
  }

  public function setTitulo($sTitulo) {
    $this->sTitulo = $sTitulo;
  }

  /**
   * Salva no banco as modificacoes do comando cvsgit push
   *
   * @access public
   * @return void
   */
  public function salvar() {

    $aArquivosCommitados = $this->aArquivos;
    $sTituloPush = $this->sTitulo;

    $oArquivoModel = new ArquivoModel();
    $oDataBase = $this->getDataBase();
    $oDataBase->begin();

    /**
     * Cria header do push 
     * @var integer $iPull - pk da tabela pull
     */
    $iPull = $oDataBase->insert('pull', array(
      'project_id' => $this->getProjeto()->id,
      'title'      => $sTituloPush,
      'date'       => date('Y-m-d H:i:s')
    ));

    /**
     * Percorre array de arquivos commitados e salva no banco
     */
    foreach ( $aArquivosCommitados as $oCommit ) {

      $iTag = $oCommit->iTag;

      if ( !empty($oCommit->iTagRelease) ) {
        $iTag = $oCommit->iTagRelease;
      }

      $oDataBase->insert('pull_files', array(
        'pull_id' => $iPull,
        'name'    => $oCommit->sArquivo,
        'type'    => $oCommit->sTipoAbreviado,
        'tag'     => $iTag,
        'message' => $oCommit->sMensagem
      ));

      /**
       * Remove arqui da lista para commit 
       */
      $oArquivoModel->removerArquivo($oCommit->sArquivo);
    } 

    $oDataBase->commit();
  }

}