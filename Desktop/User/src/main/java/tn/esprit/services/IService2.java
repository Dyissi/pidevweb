package tn.esprit.services;

import java.sql.SQLException;
import java.util.List;

public interface IService2<T> {
    void add(T t) throws Exception;
    void addP(T t) throws Exception;
    void delete(int id) throws SQLException;
    void update(int id,T t) throws SQLException;
    List<T> returnList() throws SQLException;


}