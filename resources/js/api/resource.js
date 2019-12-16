import request from '@/utils/request';

/**
 * Simple RESTful resource class
 */
class Resource {
  constructor(uri) {
    this.uri = uri;
  }
  oasisGet(sql) {
    return request({
      url: 'https://bbk.800app.com//uploadfile/staticresource/238592/279833/api_auto_json.aspx',
      method: 'get',
      params: { sql1: sql },
    });
  }
  list(query) {
    return request({
      url: '/' + this.uri,
      method: 'get',
      params: query,
    });
  }
  get(id) {
    return request({
      url: '/' + this.uri + '/' + id,
      method: 'get',
    });
  }
  store(resource) {
    return request({
      url: '/' + this.uri,
      method: 'post',
      data: resource,
    });
  }
  update(id, resource) {
    return request({
      url: '/' + this.uri + '/' + id,
      method: 'put',
      data: resource,
    });
  }
  destroy(id) {
    return request({
      url: '/' + this.uri + '/' + id,
      method: 'delete',
    });
  }
}

export { Resource as default };
