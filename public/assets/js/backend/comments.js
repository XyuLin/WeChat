define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {


            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'comments/index',
                    add_url: 'comments/add',
                    edit_url: 'comments/edit',
                    del_url: 'comments/del',
                    multi_url: 'comments/multi',
                    table: 'comments',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'article_id', title: __('Article_id'),visible:false},
                        {field: 'article_title', title: __('Article_id'),operate:false},
                        {field: 'user_id', title: __('User_id'),operate:false,visible: false},
                        {field: 'user_name', title: __('User_id'),operate:false},
                        {field: 'comments', title: __('Comments'),operate:false},
                        {field: 'parent_id', title: __('Parent_id'),operate:false,visible:false},
                        {field: 'parent_name', title: __('Parent_id'),operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime,operate:false},
                        {field: 'likes', title: __('Likes'),operate:false},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('按钮组'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: __('回复'),
                                    title: __('回复'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-commenting-o',
                                    url: 'comments/edit',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    },
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        return true;
                                    }
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });


            // $('.btn-editone').attr('data-original-title','回复');
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };

    return Controller;
});