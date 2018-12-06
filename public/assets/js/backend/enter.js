define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'enter/index',
                    add_url: 'enter/add',
                    edit_url: 'enter/edit',
                    del_url: 'enter/del',
                    multi_url: 'enter/multi',
                    table: 'enter',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'city_id', title: __('City_id')},
                        {field: 'city_name', title: __('City_name')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'full_name', title: __('Full_name')},
                        {field: 'jointime', title: __('Jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'wechat_id', title: __('Wechat_id')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'),buttons: [
                                {name: 'detail', text: '通过', title: '通过',hidden:function(row, value, index){
                                        // console.log(row);
                                        return row.status == 0 ? false : true;
                                    } ,icon: 'fa fa-check', classname: 'btn btn-xs btn-success btn-ajax', url: 'enter/operation?type=1',success:function(){ $(".btn-refresh").trigger("click");}},
                                {name: 'detail', text: '否决', title: '否决',hidden:function(row, value, index){
                                        return row.status == 0  ? false : true;
                                    } ,icon: 'fa fa-close', classname: 'btn btn-xs btn-warning btn-ajax', url: 'enter/operation?type=2',success:function(){ $(".btn-refresh").trigger("click");}},
                            ], table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    var filter = {};
                    if (value !== '') {
                        filter[field] = value;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});