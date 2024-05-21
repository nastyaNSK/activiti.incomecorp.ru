<!DOCTYPE html>
<html lang="ru">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"></script>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <link href="<?=$BackUrl;?>css/style.css" rel="stylesheet">
    <style>



    </style>
    <title>База активити Income Media</title>
    <meta name="description" content="База активити Income Media" />
</head>
<body>
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">База активити</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Поддержка</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="about-tab" data-bs-toggle="tab" data-bs-target="#about-tab-pane" type="button" role="tab" aria-controls="about-tab-pane" aria-selected="false">Разработка</button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
    <div class="d-flex">
        <div class="activity-wrap mt-3 section-wrap section" >
            <?

            foreach ($sections as $k=>$item) { ?>
                <div class="section-block" data-section="<?= implode(",", $item['activityCode']);?>">
                    <div class="activity-header <? if(isset($item['active'])) echo 'active'; ?>">
                        <?=$item['name'];?>
                    </div>
                </div>
            <?}?>
        </div>


        <div class="activity-wrap mt-3" >


            <?
            foreach ($activity as $k=>$item) { ?>
                <div class="activity-block" id="act-<?=$item['activityCode'];?>">
                    <div class="activity-header" style="">
                        <?=$item['activityName'];?>
                    </div>
                    <div class="activity-desc">
                        <p><?=$item['activityDescription'];?></p>
                        <div class="activity-detail"></div>
                    </div>
                    <div class="button-box button-box_disable" style="display: none" onclick="act_disable('<?=$item['activityCode'];?>','<?= isset($item['activityMulti']);?>')">
                        <div class="button-disable" >
                            <span class="act-link" >Удалить</span>
                        </div>
                    </div>
                    <div class="button-box button-box_enable" onclick="act_enable('<?=$item['activityCode'];?>')">
                        <div class="button-enable" >
                            <span class="act-link"  >Установить</span>
                        </div>
                    </div>
                    <div class="button-box button-box_setup" style="display: none">
                        <div class="button-setup button-enable"  >
                            <span class="act-link" >Установка</span>
                        </div></div>

                </div>
            <? } ?>
        </div>
    </div>
    </div>

        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">

            <div class="support-wrap mt-3">
                <h3 style="margin-top: 15px;" class="support-header">Отправить запрос в техническую поддержку</h3>
                <form action="" id="support-request">
                    <input type="hidden" name="member_id" value="<?=$_REQUEST['member_id']?>">
                    <input type="hidden" name="DOMAIN" value="<?=$_REQUEST['DOMAIN']?>">
                    <div class="mb-3">
                        <p>Для обращения в поддержку заполните E-mail или телефон, после чего введите ваше обращение</p>
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="sale@income-media.ru" required>
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="phone" class="form-control" id="phone" name="phone" placeholder="+79930218497" required>
                    </div>
                    <div class="mb-3">
                        <label for="supportrequest" class="form-label">Ваше обращение</label>
                        <textarea class="form-control" id="supportrequest" name="supportrequest" rows="5"></textarea>
                        <sapn class="supportrequest_wrong" style="display: none">Попробуйте сформулировать свой вопрос подробнее</sapn>
                    </div>
                    <div>
                        <button class="btn btn-success button_form">Отправить</button>
                    </div>
                </form>
                <div class="request-success" style="display: none;" >
                    <h3>Ваш запрос успешно отправлен</h3>
                    <p>В ближайшее время мы свяжемся с вами</p>
                </div>
            </div>
        </div>
    <div class="tab-pane fade" id="about-tab-pane" role="tabpanel" aria-labelledby="about-tab" tabindex="0">
        <div class="about-wrap">
            <div class="about-title">
               <div>
                    <img src="<?=$BackUrl?>images/logo.svg" alt="">
                </div>
                <div>
                    <p>Мы делаем не просто сайты и приложения, а полноценные работающие продукты, и предоставляем сопроводительные услуги полного цикла</p>
                </div>
            </div>
            <div class="about-text">
               <h5>Наши услуги</h5>
                <hr>
                <p>- Профессиональное внедрение Битрикс24;</p>
                <p>- Разработка бизнес-процессов в Битрикс24;</p>
                <p>- Настрайка телефонии любой сложности;</p>
                <p>- Разработка приложений для Битрикс24;</p>
                <p>- Внедрение Битрикс24 на коробке;</p>
                <p>- Обучение работе в Битрикс24.</p>

                <br>
                <h5>Наши контакты</h5>
                <hr>
                <p>Открытая линия в чате Битрикс24</p>
                <p>+7 (383) 381-57-93</p>
                <p>+7 (993) 021-84-97</p>
                <p></p>sale@income-media.ru</p>
                <p><a href="https://income-media.ru/" target="_blank">income-media.ru</a> </p>

            </div>
        </div>
    </div>
</div>
<script>
    $("#supportrequest").on('focus', function() {
        $('#supportrequest').removeClass('supportrequest-invalid');
        $('.supportrequest_wrong').hide();
    });


    $('#support-request').on('submit', function(e) {
        e.preventDefault();

        let textLen =  $('#supportrequest').val().length;
        if(textLen < 20) {
            $('#supportrequest').addClass('supportrequest-invalid');
            $('.supportrequest_wrong').show();
            return;
        }

        var sendInfo = $(this).serialize();

        $.ajax({
            url:    '<?=$supportBackUrl ?>',
            type:     "POST",
            dataType: "json",
            data: sendInfo,
            success: function(response) {
                $("#support-request").hide();
                $("#support-header").hide();
                $(".request-success").show();
            },
            error: function(response) {

            }
        });

    });
</script>

    <script src="//api.bitrix24.com/api/v1/"></script>
    <script>
        BX24.init(function() {
            checkList();
        });
        function act_disable(act, multi) {
            if(multi == "1")
            {
                BX24.callMethod(
                    'bizproc.activity.delete',
                    {
                        'code' : "A." + act
                    },
                    function(result)
                    {
                        if(result.error())
                        {
                            //alert('Error: ' + result.error());
                        }
                        else
                            $("#act-"+ act).removeClass("activity-enable");
                        $("#act-"+ act).find(".button-box_disable").hide();
                        $("#act-"+ act).find(".button-box_enable").show();
                    }
                );
                BX24.callMethod(
                    'bizproc.robot.delete',
                    {
                        'code' : "R." + act
                    },
                    function(result)
                    {
                        if(result.error())
                        {
                            //alert('Error: ' + result.error());
                        }
                        else
                            $("#act-"+ act).removeClass("activity-enable");
                        $("#act-"+ act).find(".button-box_disable").hide();
                        $("#act-"+ act).find(".button-box_enable").show();
                    }
                );
            }else
            {
                BX24.callMethod(
                    'bizproc.activity.delete',
                    {
                        'code' : act
                    },
                    function(result)
                    {
                        if(result.error())
                        {
                            //alert('Error: ' + result.error());
                        }
                        else
                            $("#act-"+ act).removeClass("activity-enable");
                        $("#act-"+ act).find(".button-box_disable").hide();
                        $("#act-"+ act).find(".button-box_enable").show();
                    }
                );
            }


        }
        function checkList() {
            BX24.callMethod(
                'bizproc.robot.list',
                {},
                function (result) {
                    if (result.error()){
                        //alert("Ошибка: " + result.error());
                    }else{
                        let items = result.data();

                        for (let key in items) {
                            let id = items[key].split('.');
                            if(id[1] === undefined) {
                                id[1] = items[key];
                            }
                            $("#act-"+ id[1]).addClass("activity-enable");
                            $("#act-"+ id[1]).find(".button-box_disable").show();
                            $("#act-"+ id[1]).find(".button-box_enable").hide();
                            $("#act-"+ id[1]).find(".button-box_setup").hide();
                        }
                    }
                }
            );
        }

        function act_enable(act) {

            console.log(act);

            $("#act-"+act).find(".button-box_enable").hide();
            $("#act-"+ act).find(".button-box_setup").show();

            sendInfo = {
                'member_id' : '<?=$_REQUEST['member_id'] ?>',
                'code' : act,
            }


            $.ajax({
                url:    '<?=$handlerBackUrl ?>',
                type:     "POST",
                dataType: "json",
                data: sendInfo,
                success: function(response) {
                    checkList();
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }


    </script>
    <script>
        (function(w,d,u){
            var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
            var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://work.income-media.ru/upload/crm/site_button/loader_1_nbwetz.js');
    </script>

    <script>
        $( document ).ready(function() {
            $(".section-block").on("click", function () {

                $(".activity-header").removeClass('active');
                $(this).find('.activity-header').addClass('active');

                let data = $(this).attr('data-section');
                if(data == "*")
                {
                    $(".activity-block").show();
                }else{
                    $(".activity-block").hide();
                    data = data.split(",");
                    for (let key in data)
                    {
                        let t = "#act-"+data[key];
                        $(t).show();
                    }
                }
            });
            $(".button-row").on("click", function () {



                $(".section-wrap").show();


            });

        });
    </script>

</body>
</html>