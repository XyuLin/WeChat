define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/index',
                    add_url: 'article/add',
                    edit_url: 'article/edit',
                    del_url: 'article/del',
                    multi_url: 'article/multi',
                    table: 'article',
                }
            });
            var table = $("#table");

            //在普通搜索渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='user_id']", form).addClass("selectpage").data("source", "user/user/index").data("primaryKey", "id").data("field", "nickname").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // searchFormVisible: false,
                // searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'block_category_id', title: __('Block_category_id'),visible:false,operate:false},
                        {field: 'block_category_title', title: __('Block_category_id'),operate:false},
                        {field: 'user_id', title: __('User_id'),visible:false},
                        {field: 'user_name', title: __('User_id'),operate:false,operate:false},
                        {field: 'title', title: __('Title')},
                        {field: 'images', title: __('Images'),operate:false, formatter: Table.api.formatter.images},
                        {field: 'shares', title: __('Shares'),operate:false},
                        {field: 'likes', title: __('Likes'),operate:false},
                        {field: 'comments', title: __('Comments'),operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        video: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/video',
                    add_url: 'article/videoAdd',
                    edit_url: 'article/edit',
                    del_url: 'article/del',
                    multi_url: 'article/multi',
                    table: 'article',
                }
            });
            var table = $("#table");

            //在普通搜索渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='user_id']", form).addClass("selectpage").data("source", "user/user/index").data("primaryKey", "id").data("field", "nickname").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // searchFormVisible: false,
                // searchFormTemplate: 'customformtpl',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'user_id', title: __('User_id'),visible:false},
                        {field: 'user_name', title: __('User_id'),operate:false,operate:false},
                        {field: 'title', title: __('Title')},
                        {field: 'images', title: __('Images'),operate:false, formatter: Table.api.formatter.images},
                        {field: 'shares', title: __('Shares'),operate:false},
                        {field: 'likes', title: __('Likes'),operate:false},
                        {field: 'comments', title: __('Comments'),operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });



            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        videoadd: function () {
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