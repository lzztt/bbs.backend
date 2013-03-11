<div class="front-items">
性别年龄分布
    <table id="ageGroup">
        <tbody><tr>
                <th>性别</th>
                <th>年龄</th>
                <th>人数</th>
            </tr>
            <?php foreach ($ageGroup as $i => $a): ?>
                <tr <?php echo ($i % 2 == 0) ? '' : 'class="alt"'; ?> >
                    <td><?php echo $a['sex'] ? '男' : '女'; ?></td>
                    <td><?php echo $a['age']; ?></td>
                    <td><?php echo $a['count']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<br />
    <table id="attendees">
        <tbody><tr>
                <th></th>
                <th>姓名</th>
                <th>性别</th>
                <th>电子邮箱</th>
                <th>报名时间</th>
                <th>留言</th>
            </tr>
            <?php foreach ($attendees as $i => $a): ?>
                <tr <?php echo ($i % 2 == 0) ? '' : 'class="alt"'; ?> >
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo $a['name']; ?></td>
                    <td><?php echo $a['sex'] ? '男' : '女'; ?></td>
                    <td><?php echo $a['email']; ?></td>
                    <td><?php echo date('m/d H:i', $a['time']); ?></td>
                    <td style="width: 40%"><?php echo nl2br($a['body']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<br />
</div>

<style type="text/css">
#ageGroup {
    border-collapse: collapse;
    font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
    width: 100%;
}

#ageGroup th {
    background-color: #A7C942;
    color: #FFFFFF;
    font-size: 1.4em;
    padding-bottom: 4px;
    padding-top: 5px;
    text-align: left;
}

#ageGroup td, #ageGroup th {
    border: 1px solid #98BF21;
    padding: 3px 7px 2px;
}

#ageGroup tr.alt td {
    background-color: #EAF2D3;
    color: #000000;
}
#attendees {
    border-collapse: collapse;
    font-family: "Trebuchet MS",Arial,Helvetica,sans-serif;
    width: 100%;
}

#attendees th {
    background-color: #A7C942;
    color: #FFFFFF;
    font-size: 1.4em;
    padding-bottom: 4px;
    padding-top: 5px;
    text-align: left;
}

#attendees td, #attendees th {
    border: 1px solid #98BF21;
    padding: 3px 7px 2px;
}

#attendees tr.alt td {
    background-color: #EAF2D3;
    color: #000000;
}
</style>
