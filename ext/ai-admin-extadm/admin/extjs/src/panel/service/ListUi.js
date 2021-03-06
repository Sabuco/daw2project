/*!
 * Copyright (c) Metaways Infosystems GmbH, 2011
 * LGPLv3, http://opensource.org/licenses/LGPL-3.0
 */

Ext.ns('MShop.panel.service');

MShop.panel.service.ListUi = Ext.extend(MShop.panel.AbstractListUi, {

    recordName : 'Service',
    idProperty : 'service.id',
    siteidProperty : 'service.siteid',
    itemUiXType : 'MShop.panel.service.itemui',

    autoExpandColumn : 'service-list-label',

    filterConfig : {
        filters : [{
            dataIndex : 'service.label',
            operator : '=~',
            value : ''
        }]
    },

    initComponent : function() {
        this.title = MShop.I18n.dt('admin', 'Service');

        MShop.panel.AbstractListUi.prototype.initActions.call(this);
        MShop.panel.AbstractListUi.prototype.initToolbar.call(this);

        MShop.panel.service.ListUi.superclass.initComponent.call(this);
    },

    getColumns : function() {
        return [{
            xtype : 'gridcolumn',
            dataIndex : 'service.id',
            header : MShop.I18n.dt('admin', 'ID'),
            sortable : true,
            width : 50,
            editable : false,
            hidden : true
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.status',
            header : MShop.I18n.dt('admin', 'Status'),
            sortable : true,
            width : 70,
            align : 'center',
            renderer : this.statusColumnRenderer.createDelegate(this)
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.typename',
            header : MShop.I18n.dt('admin', 'Type'),
            width : 100
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.code',
            header : MShop.I18n.dt('admin', 'Code'),
            sortable : true,
            width : 100,
            editable : false
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.provider',
            header : MShop.I18n.dt('admin', 'Provider'),
            sortable : true,
            width : 100,
            editable : false
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.label',
            header : MShop.I18n.dt('admin', 'Label'),
            sortable : true,
            width : 100,
            editable : false,
            id : 'service-list-label'
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.position',
            header : MShop.I18n.dt('admin', 'Position'),
            sortable : true,
            width : 100,
            editable : false
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.config',
            header : MShop.I18n.dt('admin', 'Configuration'),
            width : 200,
            editable : false,
            renderer : function(value) {
                var s = "";
                Ext.iterate(value, function(key, value, object) {
                    if(typeof value === "object") {
                        value = Ext.util.JSON.encode(value);
                    }
                    s = s + String.format('<div>{0}: {1}</div>', key, value);
                }, this);
                return s;
            }
        }, {
            xtype : 'datecolumn',
            dataIndex : 'service.ctime',
            header : MShop.I18n.dt('admin', 'Created'),
            sortable : true,
            width : 130,
            format : 'Y-m-d H:i:s',
            hidden : true
        }, {
            xtype : 'datecolumn',
            dataIndex : 'service.mtime',
            header : MShop.I18n.dt('admin', 'Last modified'),
            sortable : true,
            width : 130,
            format : 'Y-m-d H:i:s',
            hidden : true
        }, {
            xtype : 'gridcolumn',
            dataIndex : 'service.editor',
            header : MShop.I18n.dt('admin', 'Editor'),
            sortable : true,
            width : 130,
            hidden : true
        }];
    }

});

Ext.reg('MShop.panel.service.listui', MShop.panel.service.ListUi);

// hook this into the main tab panel
Ext.ux.ItemRegistry.registerItem('MShop.MainTabPanel', 'MShop.panel.service.listui', MShop.panel.service.ListUi, 50);
