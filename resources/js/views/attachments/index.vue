<template>
  <div id='app'>
    <el-upload
      class='upload-demo'
      drag
      action='/api/oss?resourceUrl=attachment/download'
      :on-success='success'
      multiple>
      <i class='el-icon-upload'></i>
      <div class='el-upload__text'>将文件拖到此处，或<em>点击上传</em></div>
    </el-upload>
    <el-table  :data='list' style='width: 100%' align='center'>
      <el-table-column
        type='index'
        label='序号'
        width='100'>
      </el-table-column>
      <el-table-column
        prop='name'
        label='文件名'
        width='300'>
      </el-table-column>
      <el-table-column
        prop='size'
        label='文件大小'
        width='180'>
      </el-table-column>
      <el-table-column
        prop='time'
        label='最近一次上传时间'
        width='300'>
      </el-table-column>
      <el-table-column
        label='操作'
        width='100'>
        <template slot-scope='scope'>
          <a :href='url+scope.row.name'   title='图片,txt点击右键另存下载'>下载</a>
          <el-button v-if='isShow' type='text' @click='del(scope.row.name)' class='dete'>删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import Resource from '@/api/resource';
import axios from 'axios';
const attachmentsRes = new Resource('oss');

export default {
  name: 'App',
  data() {
    return {
      isShow: false,
      list: [],
      url: 'https://tlgc.oss-cn-shanghai.aliyuncs.com/attachment/download/'
    };
  },
  computed: {

  },
  watch: {

  },
  mounted(){
    this.getAcl();
  },
  methods: {
    del(name) {
      this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(async() => {
        const { success } = await attachmentsRes.delete(name, { resourceUrl: 'attachment/download' });
        if (success) {
          this.$message({
            type: 'success',
            message: '删除成功!',
          });
          this.getList();
        } else {
          this.$message({
            type: 'error',
            message: '操作失败!',
          });
        }
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        });
      });
    },
    success() {
      this.$message({
        type: 'success',
        message: '上传成功!',
      });
      this.getList();
    },
    async getList(){
      // this.list = [];
      const res = await attachmentsRes.list({ resourceUrl: 'attachment/download' });
      if (res.success) {
        const temp = res.data;
        temp.map(item => {
          item.name = item.name.split('/').pop();
          item.time = this.utcToDate(item.time);
          return item;
        });
        this.list = temp;
      }
    },
    formatFunc(str){
      return str > 9 ? str : '0' + str;
    },
    utcToDate(utctime){
      if (!utctime) {
        return '-';
      }
      const date2 = new Date(utctime);
      const year = date2.getFullYear();
      const mon = this.formatFunc(date2.getMonth() + 1);
      const day = this.formatFunc(date2.getDate());
      const hour = this.formatFunc(date2.getHours());
      const min = this.formatFunc(date2.getMinutes());
      const dateStr = year + '-' + mon + '-' + day + ' ' + hour + ':' + min;
      return dateStr;
    },
    // 权限查询
    async getAcl(){
      const url = 'https://bbk.800app.com//uploadfile/staticresource/238592/279833/api_auto_json.aspx';
      let sql_quanxian = 'select crm_jiandang from crm_yh_238592_view where id=iduser';
      const { iduser } = this.$route.query;
      if (iduser){
        sql_quanxian = sql_quanxian.replace(/iduser/ig, iduser);
      }
      let acl = await axios.get(url, {
        params: { sql1: sql_quanxian }
      })
      acl = acl && acl.data;
      const allowed = ['系统管理员', '运营顾问'];
      if (acl && allowed.includes(acl)){
        this.getList();
        this.isShow = true;
        return;
      }
      alert('非法访问或权限不够'); return false;
    }
  }
};
</script>

<style>
  #app .el-upload-list__item{
    width: 70%;
  }
  #app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    margin-top: 60px;
  }
  a{
    text-decoration: none;
  }
  .dete{
    margin-left: 10px;
  }
</style>
