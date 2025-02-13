<div style='display: flex; justify-content: space-between; padding: 10px;'>
    <div id='totalUsers' style='font-weight: normal; color: #535c69;'>Всего: <?= $totalRow; ?></div>
    <div style='display: -webkit-inline-box;'>
        <span>Страницы: </span>
        <?php echo $pages; ?>
    </div>
    <div style='display: -webkit-inline-box;'>
        <div style='height: 38px; padding-top: 5px;'>На странице: </div>
        <select id='selectUserListCount' class='form-control' onchange='inPageSelect();' style='margin-left: 5px; max-width: 80px; height: 38px;'>
            <?php echo $select; ?>
        </select>
    </div>
</div>
