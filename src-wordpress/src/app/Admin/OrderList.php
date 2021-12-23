<div class="wrap" id="app-container">
  <h1 class="wp-heading-inline"><?php _e('Orders', 'cake'); ?></h1>
  <hr class="wp-header-end" />
  <div class="wp-clearfix">
    <el-form :inline="true" :model="query" class="vue-search-form" size="mini">
      <el-form-item label="订单编号">
        <el-input v-model="query.order_number" placeholder="订单编号" clearable></el-input>
      </el-form-item>
      <el-form-item label="客人">
        <el-input v-model="query.billing_phone" placeholder="手机号码" clearable></el-input>
      </el-form-item>

      <el-form-item label="派单编号">
        <el-input v-model="query.pickup_number" placeholder="派单编号" clearable style="width:100px;"></el-input>
      </el-form-item>
      <el-form-item label="时间">
        <el-date-picker v-model="query.datetime_range" clearable type="datetimerange" value-format="yyyy-MM-dd HH:mm:ss" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" :loading="loading" @click="onSubmit">查询</el-button>
      </el-form-item>
    </el-form>
  </div>

  <?php

  ?>

  <div class="card-body" v-loading="loading">
    <el-table :data="tableData" style="width: 100%">
      <el-table-column type="expand">
        <template slot-scope="{row}">
          <el-form label-position="left" class="demo-table-expand">
            <el-form-item label="ID">{{row.id}}</el-form-item>
            <el-form-item label="收货人信息">
              <el-button type="text">姓名：{{row.shipping_name}}</el-button>
              <el-button type="text">电话：{{row.shipping_phone}}</el-button>
              <el-button type="text">地址：{{row.shipping_address}}</el-button>
            </el-form-item>

            <el-form-item v-if="row.items.length" v-for="(item, index) in row.items" label="商品">
              <el-button type="text">姓名：{{item.product_name}}</el-button>
              <el-button type="text">价格：{{item.price}}</el-button>
              <el-button type="text">数量：{{item.quantity}}</el-button>
              <el-button type="text">小计{{item.total}}</el-button>
            </el-form-item>

            <!-- note -->
            <el-form-item label="备注">{{row.note}}</el-form-item>
          </el-form>
        </template>
      </el-table-column>
      <el-table-column prop="order_number" label="订单号" width="100"></el-table-column>
      <el-table-column prop="billing_store" label="店铺"></el-table-column>
      <!-- created_at -->
      <el-table-column prop="created_at" label="下单时间" width="180"></el-table-column>
      <el-table-column prop="pickup_time" label="取货时间" width="180"></el-table-column>
      <!-- pickup_number -->
      <el-table-column prop="pickup_number" label="派单编号" width="180"></el-table-column>
      <!-- total -->
      <el-table-column prop="total" label="订单金额" width="180"></el-table-column>
      <!-- billing_name -->
      <el-table-column label="客户" width="200">
        <template slot-scope="{ row }">
          {{row.billing_name}} | {{row.billing_phone}}
        </template>
      </el-table-column>
      <!-- 收货人 -->
      <el-table-column label="收货人" width="200">
        <template slot-scope="{ row }">
          <el-popover placement="top-start" title="地址" width="200" trigger="hover" :content="row.shipping_address">
            <el-button slot="reference" size="mini" type="text">{{row.shipping_name}} | {{row.shipping_phone}}</el-button>
          </el-popover>
        </template>
      </el-table-column>
      <!-- order_status -->
      <el-table-column prop="order_status" label="订单状态" width="180"></el-table-column>
      <!-- order_type -->
      <el-table-column prop="order_type" label="订单类型" width="180"></el-table-column>
      <!-- payment_method -->
      <el-table-column prop="payment_method" label="支付方式" width="180"></el-table-column>

      <!-- <el-table-column fixed="right" label="操作" width="80">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleEdit(scope.$index, scope.row)">编辑</el-button>
        </template>
      </el-table-column> -->
    </el-table>

    <el-pagination @current-change="changePage" :page-size="query.per_page" style="margin-top:30px;" background layout="prev, pager, next" :total="total">
    </el-pagination>
  </div>

</div>

<script>
  new Vue({
    el: '#app-container',
    data() {
      return {
        loading: true,
        wpApiSettings: window.wpApiSettings,
        tableData: [],
        total: 0,
        query: {
          billing_phone: undefined,
          order_number: undefined,
          pickup_number: undefined,
          datetime_range: undefined,
          page: 1,
          per_page: 15,
          orderby: "id",
          order: "desc"
        },
      }
    },
    mounted() {
      this.$nextTick(function() {
        this.getOrders()
      })
    },
    watch: {},
    methods: {
      getOrders() {
        this.loading = true;
        var request = jQuery.ajax({
          url: wpApiSettings.root + 'oms/v1/orders',
          method: "GET",
          data: this.query,
          dataType: "json",
          beforeSend: (xhr) => {
            xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
          }
        })

        request.done((response, textStatus, xhr) => {
          this.tableData = response.data;
          this.total = parseInt(xhr.getResponseHeader('X-WP-Total'));
          this.loading = false;
        })

        request.fail((jqXHR, textStatus) => {
          alert("Request failed: " + textStatus);
        })
      },
      changePage(page) {
        this.query.page = page
        this.getOrders()
      },
      handleEdit(index, row) {
        console.log(index, row);
      },
      onSubmit() {
        console.log(this.query);
        this.getOrders();
      }
    }
  });
</script>

<style>
  .demo-table-expand {
    font-size: 0;
    padding: 0 50px;
  }

  .demo-table-expand label {
    width: 90px;
    color: #99a9bf;
  }

  .demo-table-expand .el-form-item {
    margin-right: 0;
    margin-bottom: 0;
  }

  .vue-search-form {
    float: right;
  }
</style>