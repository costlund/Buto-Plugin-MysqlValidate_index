<?php
class PluginMysqlValidate_index{
  private $settings;
  private $mysql;
  private $i18n = null;
  function __construct() {
    wfPlugin::includeonce('wf/yml');
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/mysql');
    $this->mysql = new PluginWfMysql();
    $this->settings = wfPlugin::getPluginSettings('mysql/validate_index', true);
    wfPlugin::includeonce('i18n/translate_v1');
    $this->i18n = new PluginI18nTranslate_v1();
    $this->i18n->setPath('/plugin/mysql/validate_index/i18n');
  }
  private function db_open(){
    $this->mysql->open($this->settings->get('data/mysql'));
  }
  private function getSql($key){
    return new PluginWfYml(__DIR__.'/mysql/sql.yml', $key);
  }
  public function validate_index($field, $form, $data = array()){
    $form = new PluginWfArray($form);
    $data = new PluginWfArray($data);
    if($form->get("items/".$field."/is_valid")){
      $this->db_open();
      /**
       * 
       */
      $rs = $this->mysql->runSql("SHOW INDEX FROM ".$data->get('table_name')." where Key_name='".$data->get('key_name')."'");
      $rs = $rs['data'];
      /**
       * 
       */
      $sql = $this->getSql('db_posts');
      $sql->set('sql', str_replace('[table_name]', $data->get('table_name'), $sql->get('sql')));
      $Column_names = '';
      $params = array();
      foreach($rs as $v){
        $Column_names .= ' and '.$v['Column_name'].'=?';
        $params[] = array('type' => 's', 'value' => 'get:'.$v['Column_name']);
      }
      $Column_names = substr($Column_names, 4);
      $sql->set('sql', str_replace('[Column_names]', $Column_names, $sql->get('sql')));
      $sql->set('params', $params);
      $this->mysql->execute($sql->get());
      $rs = $this->mysql->getMany();
      /**
       * Clean up key
       */
      if(!$data->get('clean_up_key')){
        $data->set('clean_up_key', 'id');
      }
      if(wfRequest::get($data->get('clean_up_key'))){
        foreach($rs as $k => $v){
          if($v['id']==wfRequest::get($data->get('clean_up_key'))){
            unset($rs[$k]);
          }
        }
      }
      /**
       * 
       */
      if(sizeof($rs)){
        $form->set("items/$field/is_valid", false);
        $form->set("items/$field/errors/", $this->i18n->translateFromTheme('?label is duplicated.', array('?label' => $form->get("items/$field/label"))));
      }
    }
    return $form->get();
  }
}
