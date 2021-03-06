<?php

// --------------------
//  Vars
// --------------------
$func               = rex_request('func', 'string');
$aufgabe            = rex_request('aufgabe', 'string');
$filter_kat         = rex_request('filter_kat', 'string');
$eigentuemer_filter = rex_request('eigentuemer_filter', 'string');
$prio_filter        = rex_request('prio_filter', 'string');
$status_filter      = rex_request('status_filter', 'string');
$erledigt_filter    = rex_request('erledigt_filter', 'string');
$current_user       = rex::getUser()->getId();

$no_rows    = '';


if ($aufgabe == 'new') {
  // Hier die E-Mail generieren
}


// --------------------
//  Ereldigtschalter
// --------------------
if ($func == 'erledigtfilter') {

    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setTable('rex_aufgaben_filter');
    $sql->setWhere('user = '.$current_user);
    $sql->select();
    if ($sql->getRows() > 0) {

      if ($erledigt_filter == '') {
          $erledigt_filter = '0';
      }

      $sql = rex_sql::factory();
      // $sql->setDebug();
      $sql->setTable('rex_aufgaben_filter');
      $sql->setWhere('user = '.$current_user);
      $sql->setValue('erledigt', $erledigt_filter);
      $sql->update();

    } else {

      if ($erledigt_filter == '') {
        $erledigt_filter = '0';
      }

      $sql = rex_sql::factory();
      // $sql->setDebug();
      $sql->setTable('rex_aufgaben_filter');
      $sql->setValue('erledigt', '1');

      $sql->insert();
    }

    $func = '';
}

// --------------------
//  set Status
// --------------------
if ($func == 'setstatus') {
  $new_status = (rex_request('neuerstatus', 'int'));
  $id = (rex_request('id', 'int'));

  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setTable('rex_aufgaben_aufgaben');
  $sql->setWhere('id = '.$id);
  $sql->setValue('status', $new_status);

  if ($sql->update()) {
     echo '<div class="alert alert-success">Der Status wurde aktualisiert.</div>';
  }
  $func = '';
}

// --------------------
//  set Prio
// --------------------
if ($func == 'setprio') {
  $new_prio = (rex_request('neueprio', 'int'));
  $id = (rex_request('id', 'int'));

  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setTable('rex_aufgaben_aufgaben');
  $sql->setWhere('id = '.$id);
  $sql->setValue('prio', $new_prio);

  if ($sql->update()) {
     echo '<div class="alert alert-success">Die Priorität wurde aktualisiert.</div>';
  }
  $func = '';
}

// --------------------
//  Ausgabe der Tabelle
// --------------------
if ($func == '' || $func == 'filter') {

    $addsql = '';

    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setTable('rex_aufgaben_filter');
    $sql->setWhere('user = '.$current_user);
    $sql->select();
    if ($sql->getRows() > 0) {

      if ($filter_kat == '') {
          $filter_kat = $sql->getValue('kategorie');
      }
      if ($eigentuemer_filter == '') {
          $eigentuemer_filter = $sql->getValue('eigentuemer');
      }
      if ($prio_filter == '') {
          $prio_filter = $sql->getValue('prio');
      }
      if ($status_filter == '') {
          $status_filter = $sql->getValue('status');
      }

      $sql = rex_sql::factory();
      // $sql->setDebug();
      $sql->setTable('rex_aufgaben_filter');
      $sql->setValue('kategorie', $filter_kat);
      $sql->setValue('eigentuemer', $eigentuemer_filter);
      $sql->setValue('prio', $prio_filter);
      $sql->setValue('status', $status_filter);
      $sql->setWhere('user = '.$current_user);
      $sql->update();

    } else {

      if ($filter_kat == '') {
        $filter_kat = '0';
      }
      if ($eigentuemer_filter == '') {
        $eigentuemer_filter = '0';
      }
      if ($prio_filter == '') {
        $prio_filter = '0';
      }
      if ($status_filter == '') {
        $status_filter = '0';
      }

      $sql = rex_sql::factory();
      // $sql->setDebug();
      $sql->setTable('rex_aufgaben_filter');
      $sql->setValue('user',$current_user);
      $sql->setValue('kategorie', $filter_kat);
      $sql->setValue('eigentuemer', $eigentuemer_filter);
      $sql->setValue('prio', $prio_filter);
      $sql->setValue('status', $status_filter);

      $sql->insert();
    }

  if ($filter_kat != '0') {
    $addsql .= ' AND a.kategorie = '.$filter_kat;
  }
  if ($eigentuemer_filter != '0') {
    $addsql .= ' AND a.eigentuemer = '.$eigentuemer_filter;
  }
  if ($prio_filter != '0') {
    $addsql .= ' AND a.prio = '.$prio_filter;
  }
  if ($status_filter != '0') {
    $addsql .= ' AND a.status = '.$status_filter;
  }

  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setQuery('SELECT erledigt FROM rex_aufgaben_filter WHERE user = '.$current_user);
  $aktueller_erledigt_status = $sql->getValue('erledigt');

  if ($aktueller_erledigt_status == 0) {
    $where = ' a.status > 0';
  } else {
    $where = ' a.status != 6';
  }

  $query = 'SELECT  a.*,
                    a.id AS id,
                    k.id,
                    k.kategorie AS kategorie_name,
                    k.farbe
            FROM    ' . rex::getTable('aufgaben_aufgaben') . ' AS a
            LEFT JOIN  ' . rex::getTable('aufgaben_kategorien') . ' AS k
            ON a.kategorie = k.id
            WHERE ' . $where . ' ' . $addsql . ' ORDER BY a.id DESC';

  $list = rex_list::factory($query, 30, 'aufgaben');

  $list->setNoRowsMessage('<div class="alert alert-info" role="alert"><strong>Keine Aufgaben vorhanden.</strong><br/><br/>Mögliche Ursachen:<br/><br/><ul><li>es ist keine Aufgabe angelegt</li><li>keine der vorhandenen Aufgaben erfüllt auf die eingestellten Filterkriterien</li><li>die Ausgabe der erledigten Aufgaben ist abgeschaltet</li></ul></div>');
  // --------------------
  //  Edit
  // --------------------
  $tdIcon = '<i class="rex-icon rex-icon-edit"></i>';
  $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"><i class="rex-icon rex-icon-add"></i></a>';
  $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon" style="border-left: 5px solid ###farbe###">###VALUE###</td>']);
  $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
  // --------------------
  //  remove Colums
  // --------------------
  $list->removeColumn('id');
  $list->removeColumn('beschreibung');
  $list->removeColumn('farbe');
  $list->removeColumn('kategorie_farbe');
  $list->removeColumn('kategorie_id');
  $list->removeColumn('kategorie_name');
  // --------------------
  //  set Sortable
  // --------------------
  $list->setColumnSortable('kategorie');
  $list->setColumnSortable('eigentuemer');
  $list->setColumnSortable('prio');
  $list->setColumnSortable('status');
  // --------------------
  //
  //  Aufgaben (title)
  //  Beschreibung
  //
  // --------------------
  
  if ($aktueller_erledigt_status == 0) {
    $titleLink = '<a id="erledigtverbergen" class="erledigtschalter" title="Erledigte Aufgaben verbergen" href="javascript:void(0);"><i class="rex-icon fa-eye-slash"></a>';
  } else {
    $titleLink = '<a id="erledigtanzeigen" class="erledigtschalter" title="Erledigte Aufgaben anzeigen" href="javascript:void(0);"><i class="rex-icon fa-eye"></a>';
  }
  
  $list->setColumnLabel('titel', 'Aufgaben '.$titleLink);
  $list->setColumnLayout('titel', ['<th>###VALUE###</th>', '<td data-title="Aufgaben" class="td_aufgaben">###VALUE###</td>']);
  $list->setColumnFormat('titel', 'custom', function ($params) {
    $list = $params['list'];
    if ($list->getValue('beschreibung') != '') {
      $aufgabe = '<div class="collapsetitel" data-toggle="collapse" data-target="#collapse###id###">' . $list->getValue('titel') . '</div>';
    } else {
      $aufgabe = $list->getValue('titel');
    }

    if($list->getValue('beschreibung')) {
      $text = htmlspecialchars_decode($list->getValue('beschreibung'));
      if(rex_addon::get('textile')->isAvailable()) {
        $text = str_replace('<br />', '', $text);
        $text = rex_textile::parse($text);
        $text = str_replace('###', '&#x20;', $text);
      } else {
        $text = str_replace(PHP_EOL,'<br/>', $text );
      }
      $user_name = rex::getUser()->getValue('name') != '' ? rex::getUser()->getValue('name') : rex::getUser()->getValue('login');
      $text = str_replace('*****', '<div class="aufgabentrenner">'.date("d.m.y").' - '. htmlspecialchars($user_name).'</div>', $text);
      $beschreibung = '<div id="collapse###id###" class="collapse"><br/>'.$text.'</div>';
    } else {
      $beschreibung = '';
    }
    $aufgabe .= $beschreibung;
    return $aufgabe;
  });
  
  // --------------------
  //
  //  Edit
  //
  // --------------------
/*

  $list->addColumn('edit', '<i class="rex-icon rex-icon-edit"></i>');
  $list->setColumnLayout('edit', ['<th>###VALUE###</th>', '<td class="td_edit">###VALUE###</td>']);

  if ($aktueller_erledigt_status == 0) {
    $list->setColumnLabel('edit', '<a id="erledigtverbergen" class="erledigtschalter" title="Erledigte Aufgaben verbergen" href="javascript:void(0);"><i class="rex-icon fa-eye-slash"></a>');
  } else {
    $list->setColumnLabel('edit', '<a id="erledigtanzeigen" class="erledigtschalter" title="Erledigte Aufgaben anzeigen" href="javascript:void(0);"><i class="rex-icon fa-eye"></a>');
  }
  $list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###']);
  $list->addLinkAttribute('', 'class', 'rex-edit');
  */
  // --------------------
  //
  //  Kategoriefilter
  //
  // --------------------
  $kategoriefilter = '';
  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setQuery('SELECT k.* FROM rex_aufgaben_kategorien k INNER JOIN rex_aufgaben_aufgaben a ON (a.kategorie = k.id) GROUP BY k.id ORDER BY k.kategorie');
  $kategoriefilter = "<div id='kategoriefilter' class='select-style'><select>";
  $kategoriefilter .= '<option value="0">Kein Filter</option>';
  for($i=0; $i<$sql->getRows(); $i++) {
    if($sql->getValue('id') == $filter_kat) {
      $selected  = 'selected';
    } else {
      $selected  = '';
    }
    $kategoriefilter .= '<option value="'.$sql->getValue('id').'" '.$selected.'>'.$sql->getValue('kategorie').'</option>';
    $sql->next();
  }
  $kategoriefilter .= "</select></div>";
  // --------------------
  //
  //  Kategorie
  //
  // --------------------
  $list->setColumnLabel('kategorie', 'Kategorie');
  $list->setColumnLayout('kategorie', ['<th>###VALUE###<br/>'.$kategoriefilter.'</th>', '<td data-title="Kategorie" class="td_kategorie">###VALUE###</td>']);
  $list->setColumnFormat('kategorie', 'custom', function ($params) {
  $list = $params['list'];
  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setTable(rex::getTablePrefix().'aufgaben_kategorien');
  $sql->setWhere(['id'=>$list->getValue('kategorie')]);
  $sql->select();
  $kategorie = $sql->getValue('kategorie');
  return $kategorie;
  });
  // --------------------
  //
  //  Eigentümerfilter (Zuständig)
  //
  // --------------------
  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setQuery('SELECT * FROM rex_user ORDER BY name');
  $eigentuemerfilter = "<div id='eigentuemerfilter' class='select-style'><select>";
  $eigentuemerfilter .= '<option value="0" >Kein Filter</option>';
  for($i=0; $i<$sql->getRows(); $i++) {
    if($sql->getValue('id') == $eigentuemer_filter) {
      $selected  = 'selected';
    } else {
      $selected  = '';
    }
    $eigentuemerfilter .= '<option value="'.$sql->getValue('id').'" '.$selected.'>'.$sql->getValue('name').'</option>';
    $sql->next();
  }
  $eigentuemerfilter .= "</div>";
  // --------------------
  //
  //  Eigentümer (Zuständig)
  //
  // --------------------
  $list->setColumnLabel('eigentuemer', 'Zuständig');
  $list->setColumnLayout('eigentuemer', ['<th>###VALUE###<br/>'.$eigentuemerfilter.'</th>', '<td data-title="Zuständig" class="td_eigentuemer">###VALUE###</td>']);
  $list->setColumnFormat('eigentuemer', 'custom', function ($params) {
   $list = $params['list'];
   $sql = rex_sql::factory();
   // $sql->setDebug();
   $sql->setTable('rex_user');
   $sql->setWhere(['id'=>$list->getValue('eigentuemer')]);
   $sql->select();
   $eigentuemer = $sql->getValue('name');
   return $eigentuemer;
  });
  // --------------------
  //
  //  Priofilter
  //
  // --------------------
  $priofilter = "<div id='priofilter' class='select-style'><select>";
  $priofilter .= '<option value="0">Kein Filter</option>';
  for($i=1; $i<=3; $i++) {
    if($i == $prio_filter) {
      $selected  = 'selected';
    } else {
      $selected  = '';
    }
    $priofilter .= '<option value="'.$i.'" '.$selected.'>Prio '.$i.'</option>';
  }
  $priofilter .= "</select></div>";
  // --------------------
  //
  //  Prio
  //
  // --------------------
  $list->setColumnLabel('prio', 'Prio');
  $list->setColumnLayout('prio', ['<th>###VALUE###<br/>'.$priofilter.'</th>', '<td data-title="Prio" class="td_prio">###VALUE###</td>']);
  $list->setColumnFormat('prio', 'custom', function ($params) {
    $list = $params['list'];
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setTable(rex::getTablePrefix().'aufgaben_aufgaben');
    $sql->setWhere(['id'=>$list->getValue('prio')]);
    $sql->select();
    $prio = "<div class='priowrapper'>";

    for($i=0; $i<4; $i++) {
      if($list->getValue('prio') < $i) {
        $star  = 'fa-star-o';
      } else {
        $star  = 'fa-star';
      }
        $list->addLinkAttribute('prio', 'title', 'Prio: '.$i);
        $list->setColumnParams('prio', ['func' => 'setprio', 'id' => '###id###', 'neueprio' => $i]);

        if ($i == 0) {
         $prio .= $list->getColumnLink('prio', '<i class="rex-icon fa-remove "></i>');
         continue;
        }
        $prio .= $list->getColumnLink('prio', '<i class="rex-icon '.$star.' "></i>');
        $sql->next();
      }
      $prio .= "</div>";
      return $prio;
    });
  // --------------------
  //
  //  Statusfilter
  //
  // --------------------
  $statusfilter = '';
  $sql = rex_sql::factory();
  // $sql->setDebug();
  $sql->setQuery('SELECT * FROM rex_aufgaben_status ORDER BY id');
  $statusfilter = "<div id='statusfilter' class='select-style'><select>";
  $statusfilter .= '<option value="0">Kein Filter</option>';
  for($i=0; $i<$sql->getRows(); $i++) {
    if($sql->getValue('id') == $status_filter) {
      $selected  = 'selected';
    } else {
      $selected  = '';
    }
    $statusfilter .= '<option value="'.$sql->getValue('id').'" '.$selected.'>'.$sql->getValue('status').'</option>';
    $sql->next();
  }
  $statusfilter .= "</select></div>";
  // --------------------
  //
  //  Status
  //
  // --------------------
  $list->setColumnLabel('status', 'Status');
  $list->setColumnLayout('status', ['<th>###VALUE###<br/>'.$statusfilter.'</th>', '<td data-title="Status" class="td_status">###VALUE###</td>']);
  $list->setColumnFormat('status', 'custom', function ($params) {
    $list = $params['list'];
    $sql = rex_sql::factory();
    //$sql->setDebug();
    $sql->setTable(rex::getTablePrefix().'aufgaben_status');
    $sql->select();
    $status = "<div class='status'>";
    for($i=0; $i<$sql->getRows(); $i++) {
      if($list->getValue('status') == $sql->getValue('id')) {
        $current  = 'current';
      } else {
        $current  = '';
      }
        $list->addLinkAttribute('status', 'title', $sql->getValue('status'));
        $list->setColumnParams('status', ['func' => 'setstatus', 'id' => '###id###', 'neuerstatus' => $sql->getValue('id')]);
        $status .= $list->getColumnLink('status', '<i class="rex-icon '.$current.' '.$sql->getValue('icon').' "></i>');
        $sql->next();
      }
    $status .= "</div>";
    return $status;
  });


  $content = $list->get();

  $fragment = new rex_fragment();
  $fragment->setVar('content', $content, false);
  echo $fragment->parse('core/page/section.php');




} elseif ($func == 'edit' || $func == 'add') {

  $fieldset = $func == 'edit' ? 'Aufgabe editieren' : 'Aufgabe hinzufügen';
  $id = rex_request('id', 'int');

  $form = rex_form::factory(rex::getTablePrefix().'aufgaben_aufgaben', '', 'id='.$id);

  $field = $form->addTextField('titel');
  $field->setLabel('Titel');

  $field = $form->addTextareaField('beschreibung');
  $field->setLabel('Beschreibung');

  $field = $form->addSelectField('kategorie');
  $field->setLabel("Kategorie");
  $field->getValidator()->add('notEmpty', 'Bitte eine Kategorie auswählen.');
  $select = $field->getSelect();
  $select->setSize(1);
  $query = 'SELECT kategorie as label, id FROM rex_aufgaben_kategorien ORDER BY kategorie';
  $select->addOption('Bitte wählen','');
  $select->addSqlOptions($query);

  $field = $form->addSelectField('eigentuemer');
  $field->setLabel('Zuständig');
  $field->getValidator()->add('notEmpty', 'Bitte geben Sie an wer für dies Aufgabe zustädig ist.');
  $select = $field->getSelect();
  if ($func == 'add') {
        $select->setSelected($current_user);
  }
  $select->setSize(1);

  $query = 'SELECT name as label, id FROM rex_user';
  $select->addOption('Bitte wählen','');
  $select->addSqlOptions($query);

  $field = $form->addSelectField('status');
  $field->setLabel('Status');
  $select =$field->getSelect();
  $select->setSize(1);
  $query = 'SELECT status as label, id FROM rex_aufgaben_status';
  # $select->addOption('Offen',1);
  # $select->addOption('Erledigt',2);
  $select->addSqlOptions($query);

  if ($func == 'edit') {
    $form->addParam('id', $id);
  }

  if ($func == 'add') {
    $form->addParam('aufgabe', 'new');
  }

  $content = $form->get();

  $fragment = new rex_fragment();
  $fragment->setVar('class', 'edit', false);
  $fragment->setVar('title', "$fieldset");
  $fragment->setVar('body', $content, false);
  $content = $fragment->parse('core/page/section.php');

  echo $content;
}
?>
<script>
$("#kategoriefilter select").change(function(){
     $value = $("#kategoriefilter select").val();
     location.replace("index.php?page=aufgaben/aufgaben&func=filter&filter_kat="+$value );
});
$("#eigentuemerfilter select").change(function(){
     $value = $("#eigentuemerfilter select").val();
     location.replace("index.php?page=aufgaben/aufgaben&func=filter&eigentuemer_filter="+$value );
});
$("#priofilter select").change(function(){
     $value = $("#priofilter select").val();
     location.replace("index.php?page=aufgaben/aufgaben&func=filter&prio_filter="+$value );
});
$("#statusfilter select").change(function(){
     $value = $("#statusfilter select").val();
     location.replace("index.php?page=aufgaben/aufgaben&func=filter&status_filter="+$value );
});
$("#statusfilter select").change(function(){
     $value = $("#statusfilter select").val();
     location.replace("index.php?page=aufgaben/aufgaben&func=filter&status_filter="+$value );
});
$("#erledigtverbergen").click(function(){
  location.replace("index.php?page=aufgaben/aufgaben&func=erledigtfilter&erledigt_filter=1" );
});
$("#erledigtanzeigen").click(function(){
  location.replace("index.php?page=aufgaben/aufgaben&func=erledigtfilter&erledigt_filter=0" );
});

</script>
