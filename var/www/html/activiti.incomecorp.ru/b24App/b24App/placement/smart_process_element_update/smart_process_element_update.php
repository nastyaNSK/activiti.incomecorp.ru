<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<?
//include './crest/crest.php';


$options = json_decode($_REQUEST['PLACEMENT_OPTIONS'], true);
$activity_name = $options['activity_name'];
$domain = $_REQUEST['DOMAIN'];

$file = __DIR__ . '/data/' . $activity_name . $domain;
if (empty($activity_name))
    $file = $_REQUEST['file'];
    unset($_REQUEST['file']);
    

?>


<? if(!($_REQUEST['entity_type_id'] || file_exists($file))):?>
<form method="get" action="https://activiti.incomecorp.ru/placement/">
    <div class="form-group">
        <label for="entity_type_id">ID типа смарт-процесса:</label>
        <input type="text" class="form-control" id="entity_type_id" name="entity_type_id" required>
        <input type="text" name="placement" value="smart_process_element_update" style="display:none;">
        <input type="text" style="display:none;" value="<?echo $_REQUEST['member_id']?>" name="member_id">
        <input type="text" style="display:none;" value="<?echo $file?>" name="file">
    </div>
    <div class="form-group">
        <label for="id">ID элемента</label>
        <input type="text" class="form-control" id="id" name="id" required>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top: 10px">Продолжить</button>
</form>
<?else:?>

<?php

    if ($_REQUEST['entity_type_id'])
    {
        $request = [];
        foreach ($_REQUEST as $keyH => $valueH)
        {
            $request[$keyH] = $valueH;
        }
        $request = json_encode($request);
        file_put_contents($file, $request);
    }


$data = [];
if (file_exists($file))
{
    $data = file_get_contents($file);
    $data = json_decode($data, true);
    $entityTypeId = $data['entity_type_id'];
    $id = $data['id'];
}
    else
{
    $entityTypeId = $_REQUEST['entity_type_id'];
    $id = $_REQUEST['id'];
}

$fields = $this->call(
        'crm.item.fields',
        [
            'entityTypeId' => $entityTypeId
        ]
);

$fields = $fields['result']['fields'];

$unset = array();
$formField = array();

foreach ($fields as $key => $value)
{
    foreach ($value as $keyD => $valueD)
    {
        if ($keyD == "isReadOnly" && $valueD == 1)
        {
            $unset[] = $key;
        }
        if ($keyD == 'isImmutable' && $valueD == 1)
        {
            $unset[] = $key;
        }
        if ($keyD == 'title')
        {
            $formField[$key] = $valueD;
        }
    }
}

foreach ($unset as $uns)
{
    unset($formField[$uns]);
}

?>

    <form action="https://activiti.incomecorp.ru/placement/" method="get">
        <div class="form-group">
            <label for="smart_process_id">ID типа смарт-процесса:</label>
            <input type="text" class="form-control" id="entity_type_id" name="entity_type_id" value="<?=$entityTypeId?>">
        </div>
        <div class="form-group">
            <label for="id">ID элемента:</label>
            <input type="text" class="form-control" id="id" name="id" value="<?=$id?>">
        </div>
        <input type="text" name="placement" value="smart_process_element_update" style="display:none;">
        <input type="text" style="display:none;" value="<?echo $_REQUEST['member_id']?>" name="member_id">
        <?foreach ($formField as $k => $v):?>
        <div class="form-group">
            <label for="<?=$k?>"> <?=$v?> </label>
            <input type="text" name="<?=$k?>" id="<?=$k?>" class="form-control" style="margin-top: 10px" value="<?=$data[$k]?>">
        </div>
        <?endforeach;?>
        <input name="file" style="display:none;" value="<?=$file?>">
        <button type="submit" class="btn btn-primary" style="margin-top: 10px">Сохранить</button>
    </form>

<?endif;?>


<script>

    function propVal(prop)
    {
        console.log(prop.value);
        console.log(prop.name);
    }

</script>