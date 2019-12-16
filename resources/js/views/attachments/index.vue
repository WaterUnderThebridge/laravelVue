<template>
  <div id='app'>
    <router-view/>
    <el-upload
      v-if='isShow2'
      class='upload-demo'
      drag
      action='https://oss.thelittlegym.com.cn/oss/upload'
      :on-success='success'
      multiple>
      <i class='el-icon-upload'></i>
      <div class='el-upload__text'>将文件拖到此处，或<em>点击上传</em></div>
    </el-upload>

    <el-table
      :data='list'
      style='width: 100%'
      align='center'>
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
          <!-- <el-button  size='small' type='primary' icon='el-icon-download'  @click='down(scope.row.name)'>下载</el-button> -->
          <a :href='url+scope.row.name'   title='图片,txt点击右键另存下载'>下载</a>
          <el-button v-if='isShow' type='text' @click='del(scope.row.name)' class='dete'>删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isShow: false,
      isShow2: false,
      list: [],
      url: 'https://tlgc.oss-cn-shanghai.aliyuncs.com/attachment/download/'

    };
  },
  computed: {

  },
  watch: {

  },
  methods: {
    submitUpload() {
      this.$refs.upload.submit();
    },
    del(name) {
      this.$confirm('此操作将永久删除该文件, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(() => {
        let that = this;
        this.$http({
          method: 'post',
          // url:'http://localhost/del.php',
          url: 'https://oss.thelittlegym.com.cn/oss/del',
          data: {
            test: name,
          },
        }).then(function(res){
          // console.log(res.data);
          that.$message({
            type: 'success',
            message: '删除成功!',
          });
          that.getList();
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        });
      });
    },
    success(file) {
      this.getList();
    },
    getList(){
      const url = `https://oss.thelittlegym.com.cn/oss`;
      this.$http.get(url).then(resp => {
        const temp = resp.data.map(item => {
          item.name = item.name.substr(20);
          item.time = this.utcToDate(item.time);
          return item;
        })
        this.list = temp;
      });
    },

    formatFunc(str){
      return str > 9 ? str : '0' + str;
    },
    utcToDate(utctime){
      if (!utctime){
        return '-';
      }
      const date2 = new Date(utctime);
      const year = date2.getFullYear();
      const mon = this.formatFunc(date2.getMonth()+1);
      const day = this.formatFunc(date2.getDate());
      const hour = this.formatFunc(date2.getHours());
      const min = this.formatFunc(date2.getMinutes());
      const dateStr = year + '-' + mon + '-' + day + ' ' + hour + ':' + min;
      return dateStr;
    },

    // 权限查询
    getAcl(){
      const url = 'https://bbk.800app.com/uploadfile/staticresource/238592/279833/dataInterface_jsonp_uni.aspx';
      let sql_quanxian = 'select crm_jiandang from crm_yh_238592_view where id=iduser'
      const param = GetRequest()
      if(param && param.iduser){
        sql_quanxian = sql_quanxian.replace(/iduser/ig,param.iduser);
      }
      const self = this;
      this.$jsonp(url, {
        sql1: sql_quanxian
      }).then(res => {
        res = JSON.parse(res);
        const acl = res.info[0].rec[0].crm_jiandang;
        if (res.info[0].rec.constructor !== String && acl === '系统管理员' || acl === '运营顾问'){
          self.isShow2 = true;
          if (acl === '系统管理员'){
            self.isShow = true;
          }
          self.getList();
        } else {
          alert('非法访问或权限不够'); return false;
        }
      });
    }
  },
  created(){
    this.getAcl();
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
