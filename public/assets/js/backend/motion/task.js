define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'motion/task/index',
                    add_url: 'motion/task/add',
                    edit_url: 'motion/task/edit',
                    del_url: 'motion/task/del',
                    multi_url: 'motion/task/multi',
                    table: 'motion_task',
                }
            });

            var table = $("#table");

            //在普通搜索渲染后
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='module_id']", form).addClass("selectpage").data("source", "motion/module/index").data("primaryKey", "id").data("field", "title").data("orderBy", "id desc");
                Form.events.cxselect(form);
                Form.events.selectpage(form);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'module_id', title: __('Module_id'),visible: false},
                        {field: 'module_text', title: __('Module_id'),operate:false},
                        {field: 'title', title: __('Title'),operate:false},
                        {field: 'introduce', title: __('Introduce'),visible:false,operate:false},
                        {field: 'image', title: __('Image'), formatter: Table.api.formatter.image,operate:false},
                        {field: 'lenght', title: __('Lenght'),operate:false},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url,operate:false},
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
            $('#c-url').change(function () {
                var val =  'http://localhost/WeChat/public' + $('#c-url').val();
                $("#myvideo").prop("src", val);
                $("#myvideo")[0].addEventListener("loadedmetadata", function() {
                    var t = this.duration; //获取总时长
                    var length = Math.floor(t/60)+":"+(t%60/100).toFixed(2).slice(-2)
                    $('#c-lenght').val(length);

                });
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $('#c-url').change(function () {
                var val =  'http://localhost/WeChat/public' + $('#c-url').val();
                $("#myvideo").prop("src", val);
                $("#myvideo")[0].addEventListener("loadedmetadata", function() {
                    var t = this.duration; //获取总时长
                    var length = Math.floor(t/60)+":"+(t%60/100).toFixed(2).slice(-2)
                    $('#c-lenght').val(length);

                });
            });
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